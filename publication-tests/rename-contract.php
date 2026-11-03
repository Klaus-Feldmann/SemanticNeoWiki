<?php
require __DIR__.'/bootstrap.php';
$root=dirname(__DIR__);
$manifest=json_decode(file_get_contents($root.'/extension.json'),true,512,JSON_THROW_ON_ERROR);
$composer=json_decode(file_get_contents($root.'/composer.json'),true,512,JSON_THROW_ON_ERROR);
$bridge=json_decode(file_get_contents($root.'/NeoWikiBaseBridge/extension.json'),true,512,JSON_THROW_ON_ERROR);
function renameCheck(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
renameCheck($manifest['name']==='SemanticNeoWiki','registry identity');
renameCheck($manifest['ResourceFileModulePaths']['remoteExtPath']==='SemanticNeoWiki','ResourceLoader install path');
renameCheck($composer['name']==='aufeinwort/semantic-neowiki' && $composer['extra']['installer-name']==='SemanticNeoWiki','Composer installation identity');
renameCheck(isset($bridge['requires']['extensions']['SemanticNeoWiki']),'bridge registry dependency');
$badge=ProfessionalWiki\NeoWiki\NeoWikiExtension::poweredByBadge('/w/extensions','1.47.0-wmf.24');
foreach(array_merge([$badge['src']],array_column($badge['sources']??[],'srcset'))as$url){
 renameCheck(str_starts_with($url,'/w/extensions/SemanticNeoWiki/'),'new badge path');
 renameCheck(is_file($root.substr($url,strlen('/w/extensions/SemanticNeoWiki'))),'badge asset exists');
}
renameCheck(isset($manifest['config']['NeoWikiRdfBaseUri']),'existing RDF config identity retained');
foreach(glob($root.'/maintenance/*.php')as$file){
 renameCheck(!str_contains(file_get_contents($file),"requireExtension( 'NeoWiki' )"),'maintenance registry identity');
}
echo "SemanticNeoWiki registry, ResourceLoader, Composer, bridge, assets and retained data/config identities verified.\n";
