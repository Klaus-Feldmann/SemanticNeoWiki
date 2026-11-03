<?php

declare( strict_types = 1 );
namespace NeoWikiBaseBridge;

use ProfessionalWiki\NeoWiki\EntryPoints\NeoWikiRegistrar;

final class Hooks {
	public static function onNeoWikiRegistration( NeoWikiRegistrar $registrar ): void {
		$registrar->addPagePropertyProvider( new WikidataPagePropertyProvider() );
	}
	public static function onScribuntoExternalLibraries( string $engine, array &$libraries ): bool {
		if ( $engine === 'lua' && \ProfessionalWiki\NeoWiki\NeoWikiExtension::getInstance()->isLuaEnabled() ) {
			$libraries['mw.neowikibase'] = ScribuntoLuaLibrary::class;
		}
		return true;
	}

	public static function onBeforePageDisplay( \MediaWiki\Output\OutputPage $out ): void {
		$out->addJsConfigVars( 'wgNeoWikiCompleteExport', true );
	}
}
