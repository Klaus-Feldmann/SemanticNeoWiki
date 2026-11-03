<?php
/**
 * Local helper for UnlinkedWikibase 3.0.0 / MediaWiki 1.45.
 * Include from LocalSettings.php. No remote writes, no local Wikibase edits.
 * Syntax: {{#aewiwikidata:Q-ID|Wikipedia title|automatic (1/0)|missing-link category}}
 */

// null permits explicit assignments in all non-special namespaces.
$wgAewiWikidataExplicitNamespaces = $wgAewiWikidataExplicitNamespaces ?? null;
$wgAewiWikidataAutoNamespaces = $wgAewiWikidataAutoNamespaces ?? [ 0 ];
// Einmaliger SMW-Attributname; keine Q-ID-Konfiguration nötig.
// Leere Zeichenfolge deaktiviert nur die zusätzliche SMW-Annotation.
$wgAewiWikidataSmwProperty = $wgAewiWikidataSmwProperty ?? 'Wikidata-ID';

function aewiAnnotateRegisteredWikidata( $parser ) {
    global $wgAewiWikidataSmwProperty;
    $property = trim( (string)$wgAewiWikidataSmwProperty );
    $id = $parser->getOutput()->getPageProperty( 'unlinkedwikibase_id' );
    if ( $property === '' || !is_string( $id ) ||
        !preg_match( '/^Q[1-9][0-9]*$/D', $id ) ) {
        return '';
    }
    if ( strpbrk( $property, "|{}[]<>#=\r\n" ) !== false ||
        str_contains( $property, '::' ) ) {
        throw new \InvalidArgumentException( 'Ungültiger SMW-Attributname für Wikidata-ID.' );
    }
    // In den aktuellen Parserlauf aufnehmen; dauerhafte Speicherung erfolgt
    // durch SMWs reguläre Verarbeitung, nicht durch direkte Datenbankzugriffe.
    return $parser->recursiveTagParse( '{{#set:' . $property . '=' . $id . '}}' );
}


$wgExtensionMessagesFiles['AewiWikidataRegistrierungMagic'] =
    __DIR__ . '/AewiWikidataRegistrierung.i18n.magic.php';

$wgHooks['ParserFirstCallInit'][] = static function ( $parser ) {
    $parser->setFunctionHook( 'aewiwikidata', 'aewiRegisterWikidata' );
    return true;
};

function aewiRegisterWikidata( $parser, $id = '', $wikipediaTitle = '', $automatic = '1', $missingCategory = '' ) {
    global $wgAewiWikidataExplicitNamespaces, $wgAewiWikidataAutoNamespaces;
    $title = $parser->getTitle();
    if ( !$title || $title->getNamespace() < 0 || $title->isRedirect() ) {
        return '';
    }
    $id = trim( $id );
    $wikipediaTitle = trim( $wikipediaTitle );
    $explicit = $id !== '' || $wikipediaTitle !== '';
    $allowed = $explicit ? $wgAewiWikidataExplicitNamespaces : $wgAewiWikidataAutoNamespaces;
    if ( $allowed !== null && !in_array( $title->getNamespace(), $allowed, true ) ) {
        return '';
    }
    $output = $parser->getOutput();
    $notice = static function ( $message ) {
        return \MediaWiki\Html\Html::element(
            'span', [ 'class' => 'aewi-wikidata-status' ],
            'Wikidata-Zuordnung: ' . $message
        );
    };

    if ( $id !== '' && !preg_match( '/^Q[1-9][0-9]*$/D', $id ) ) {
        return $notice( 'Ungültige Q-ID.' );
    }

    // Repeated template calls must not undo an earlier registration.
    $existing = $output->getPageProperty( 'unlinkedwikibase_id' );
    if ( $existing ) {
        if ( $id !== '' && $id !== $existing ) {
            return $notice( 'Widersprüchliche Q-IDs auf derselben Seite.' );
        }
        return aewiAnnotateRegisteredWikidata( $parser );
    }

    if ( $id === '' ) {
        if ( trim( $automatic ) === '0' ) {
            return '';
        }
        if ( $wikipediaTitle === '' ) {
            $wikipediaTitle = $title->getPrefixedText();
        }
        $wikipediaTitle = str_replace( '_', ' ', $wikipediaTitle );
        if ( strpbrk( $wikipediaTitle, "|#\r\n" ) !== false ) {
            return $notice( 'Bitte einen einzelnen Wikipedia-Artikeltitel ohne Abschnitt angeben.' );
        }
        if ( !$parser->incrementExpensiveFunctionCount() ) {
            return $notice( 'Abfragelimit erreicht.' );
        }

        // Memoize within one parse, including pending/error responses.
        $memoKey = 'aewi-wikidata-title-results';
        $memo = $output->getExtensionData( $memoKey ) ?? [];
        if ( !array_key_exists( $wikipediaTitle, $memo ) ) {
            try {
                $wb = new \MediaWiki\Extension\UnlinkedWikibase\Wikibase();
                $memo[$wikipediaTitle] = $wb->getApiResult( [
                    'action' => 'wbgetentities',
                    'format' => 'json',
                    'sites' => 'dewiki',
                    'titles' => $wikipediaTitle,
                    'props' => 'info',
                    'normalize' => 1,
                ], 86400 );
            } catch ( \Throwable $e ) {
                wfDebugLog( 'AewiWikidataRegistrierung', $e->getMessage() );
                $memo[$wikipediaTitle] = [];
            }
            $output->setExtensionData( $memoKey, $memo );
        }
        $data = $memo[$wikipediaTitle];
        if ( isset( $data['error'] ) ) {
            $output->updateCacheExpiry( 60 );
            return $notice( 'Die externe API meldet einen Fehler; keine Zuordnung gesetzt.' );
        }
        if ( empty( $data['success'] ) || !isset( $data['entities'] ) ||
            !is_array( $data['entities'] ) || !$data['entities'] ) {
            $output->updateCacheExpiry( 60 );
            return $notice( 'Abruf noch ausstehend oder fehlgeschlagen; nach Jobverarbeitung erneut prüfen.' );
        }
        $ids = [];
        $missing = false;
        foreach ( $data['entities'] as $entity ) {
            if ( !is_array( $entity ) ) {
                continue;
            }
            if ( array_key_exists( 'missing', $entity ) ) {
                $missing = true;
                continue;
            }
            $candidate = $entity['id'] ?? '';
            if ( is_string( $candidate ) && preg_match( '/^Q[1-9][0-9]*$/D', $candidate ) ) {
                $ids[$candidate] = true;
            }
        }
        if ( count( $ids ) !== 1 ) {
            if ( !$ids && $missing ) {
                $categoryMarkup = '';
                $missingCategory = trim( $missingCategory );
                if ( $missingCategory !== '' ) {
                    // Parameter is the category name WITHOUT the namespace prefix.
                    $category = \MediaWiki\Title\Title::makeTitleSafe( NS_CATEGORY, $missingCategory );
                    if ( $category && !$category->isExternal() && $category->getFragment() === '' ) {
                        $categoryMarkup = '[[' . $category->getPrefixedText() . ']]';
                    }
                }
                return $notice( 'Kein Wikidata-Seitenlink für diesen Wikipedia-Titel gefunden.' ) . $categoryMarkup;
            }
            $output->updateCacheExpiry( 60 );
            return $notice( 'Antwort nicht eindeutig auswertbar; keine Zuordnung gesetzt.' );
        }
        $id = array_key_first( $ids );
    }

    // Use the extension's own registration, including its additional hooks.
    $result = $parser->recursiveTagParse( '{{#unlinkedwikibase:id=' . $id . '}}' );
    return $result . aewiAnnotateRegisteredWikidata( $parser );
}
$wgHooks['BeforePageDisplay'][] = static function ( $out, $skin ) {
    $html = $out->getHTML();
    if ( $out->getRequest()->getVal( 'action' ) !== 'info' ||
        !str_contains( $html, 'mw-unlinkedwikibase-infoaction-label' ) ) {
        return true;
    }

    $message = wfMessage( 'unlinkedwikibase-infoaction-label-url' )->inContentLanguage();
    if ( $message->isDisabled() ) {
        return true;
    }
    $target = \MediaWiki\Title\Title::newFromText( trim( $message->text() ) );
    if ( !$target || $target->isExternal() ) {
        return true;
    }

    // Nur die Beschriftung der bekannten Tabellenzeile bearbeiten.
    $pattern = '~(<tr\b[^>]*\bid="mw-unlinkedwikibase-infoaction-label"[^>]*>\s*<td\b[^>]*>)(.*?)(</td>)~s';
    $linkedHtml = preg_replace_callback( $pattern, static function ( $match ) use ( $target ) {
        if ( preg_match( '~<a\b~i', $match[2] ) ) {
            return $match[0];
        }
        $link = \MediaWiki\Html\Html::rawElement(
            'a', [ 'href' => $target->getLocalURL() ], $match[2]
        );
        return $match[1] . $link . $match[3];
    }, $html );
    if ( $linkedHtml !== null && $linkedHtml !== $html ) {
        $out->clearHTML();
        $out->addHTML( $linkedHtml );
    }
    return true;
};

