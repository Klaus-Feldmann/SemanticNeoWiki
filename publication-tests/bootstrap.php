<?php
$root=dirname(__DIR__).'/';
$vendor=getenv('NEOWIKI_TEST_VENDOR') ?: $root.'vendor/autoload.php';
if(is_file($vendor))require $vendor;
spl_autoload_register(static function(string $class)use($root): void {
 foreach(['ProfessionalWiki\\NeoWiki\\Tests\\'=>'tests/phpunit/','ProfessionalWiki\\NeoWiki\\'=>'src/','NeoWikiBaseBridge\\'=>'NeoWikiBaseBridge/src/']as $prefix=>$directory){
  if(str_starts_with($class,$prefix)){
   $file=$root.$directory.str_replace('\\','/',substr($class,strlen($prefix))).'.php';
   if(is_file($file))require $file;
  }
 }
},true,true);
