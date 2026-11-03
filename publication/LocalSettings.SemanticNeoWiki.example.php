<?php
/** Selected non-secret settings from the tested AufEinWort setup.
 * Merge into the existing LocalSettings; do not replace the whole file.
 * Original NeoWiki/Bridge load lines and an older registration include must
 * be removed/replaced for the switch. Load each component only once.
 * Prerequisites retain their existing installation/loading configuration.
 */
$wgUseParsoidParser = false;
$wgUseParsoidLinksUpdate = false;

wfLoadExtension( 'SemanticNeoWiki' );
wfLoadExtension( 'NeoWikiBaseBridge', "$IP/extensions/SemanticNeoWiki/NeoWikiBaseBridge/extension.json" );
$wgNeoWikiEnableLua = true;
$wgNeoWikiAutoRenderMainSubject = false;
$wgNeoWikiSubjectFirst = false;

// This is the separate NeoWiki graph dataset. Preserve these deployed values.
$wgNeoWikiSparqlStores = [
    [
        'name' => 'aewi-neowiki',
        'updateUrl' => 'http://127.0.0.1:3331/neowiki/update',
        'queryUrl' => 'http://127.0.0.1:3331/neowiki/query',
        'projection' => 'native',
    ],
];
// Do not change the existing RDF base IRI when renaming the extension.
// Set NeoWikiRdfBaseUri only if the existing deployment already sets it.

// Existing local Wikibase sitelink site IDs; other deployments use their own IDs.
$wgWBClientSettings['siteGlobalID'] = 'aewi';
$wgWBRepoSettings['siteGlobalID'] = 'aewi';

// Existing registration helper. Replace the previous external require_once;
// do not load both copies. UnlinkedWikibase must already be installed/loaded.
$wgAewiWikidataAutoNamespaces = [ 0 ];
$wgAewiWikidataSmwProperty = 'Wikidata';
require_once "$IP/extensions/SemanticNeoWiki/integrations/registration/AewiWikidataRegistrierung.php";

// Existing SMW SPARQLStore is a DIFFERENT dataset, not the graph above.
// Keep these only where this SMW store is actually configured.
$smwgSparqlRepositoryConnector = 'fuseki';
$smwgDefaultStore = 'SMW\\SPARQLStore\\SPARQLStore';
$smwgSparqlEndpoint = [
    'query' => 'http://localhost:3030/dataset/sparql',
    'update' => 'http://localhost:3030/dataset/update',
    'data' => '',
];

// Observed MediaWiki:NeoWiki JSON remains unchanged:
// { "subjectFirst": false, "dataLanguages": ["de", "la", "grc", "en"] }
// The existing deployment has wgJobRunRate=0 and uses an external job runner.
// This example does not configure that runner or change access permissions.
