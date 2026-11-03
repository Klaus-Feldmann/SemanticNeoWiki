# Composer installation and switch from the tested NeoWiki-named fork

The sole supported installation path is Composer in an existing MediaWiki 1.47.0-wmf.24 server matching the environment in README.md. There is no supported Docker/demo install. The upstream docs retained in this repository describe the original project and are not an alternative installation channel for this fork.

## Before changing the server

Back up composer.json, composer.local.json, composer.lock, LocalSettings.php, the old NeoWiki extension directory and graph-explorer/config.js. Preserve your existing database backup. First apply the expanded MCP patch if the MCP tools must remain available after the extension is renamed. No installation command in this guide implicitly changes LocalSettings or patches SMW/Wikibase.

The current server may have loaded NeoWiki from copied files and a Composer merge include rather than as a managed package. Check which case applies. Remove old `neowiki/neowiki` requirements and the obsolete `extensions/NeoWiki/composer.json` merge include when switching; retain all unrelated merge entries and package requirements. Do not run `COMPOSER=composer.local.json composer update`, which would treat the local fragment as a separate root project.

## Install the candidate

Merge the VCS repository, package requirement and composer/installers allowance shown in README.md into your existing root/local configuration. Use `dev-semantic-0.1.9-test` explicitly. Then run the README Composer command from the MediaWiki root. The extension installer writes `extensions/SemanticNeoWiki` and its dependencies are loaded through the MediaWiki root vendor/autoload.php.

Check that `extensions/SemanticNeoWiki/extension.json` exists before changing loading instructions. Replace the NeoWiki and bundled Bridge load lines with those in README.md. The old directory may be retained for return but must not be loaded in parallel. Do not delete old dependencies, RDF data or namespace contents as part of the rename.

Keep `$wgUseParsoidParser = false` and `$wgUseParsoidLinksUpdate = false`, the confirmed legacy-parser selections. Retain the existing NeoWiki configuration names. `MediaWiki:NeoWiki` continues to provide its current allowed on-wiki settings. Source identities still come from UnlinkedWikibase page properties and local Wikibase sitelinks. A registration helper is included, but your working WikibaseQ/template workflow does not need replacement merely for the rename.

Copy your existing graph config to the new path and deliberately update your graph URL/redirect. The extension directory does not create a SPARQL proxy or move Fuseki data. Preserve the exact RDF base IRI and graph-store endpoints. A local Wikibase site-id override remains `NeoWikiBaseBridgeLocalSiteId`, if your site needs one.

## Process and check

For a switch from our installed 0.1.8-aewi runtime, the rename has no SQL/content-model migration. Rebuild localization with `php maintenance/run.php rebuildLocalisationCache --force`, refresh normal PHP/OPcache state under your established server procedure and reload the Data/Graph pages. A fresh installation requires MediaWiki's normal `php maintenance/run.php update` as well. No whole-wiki rebuild is required just for the renamed package.

Verify Special:Version reports SemanticNeoWiki and the new Bridge; open/edit/save one subject, inspect Data/Graph links and thumbnails, and download complete JSON/Turtle/TriG. Confirm the existing MCP tools and complete-view tool after connection refresh. For SMW, verify a changed and a removed value after saving the subject and running queued work. Keep a functioning job runner. Remote Wikidata changes alone do not reprocess all pages.

The preceding runtime was live-tested in AufEinWort. The renamed extension/Composer migration remains a separate live test until confirmed; local Composer checks do not boot a complete production wiki.

## Optional companion files

Import/adapt only the wiki pages documented under integrations/smw. Existing current modules need not be replaced for the extension rename. Load AewiWikidataRegistrierung.php once if this helper is used; its Magic Words file is registered by that PHP file. The optional SMW statistics patch is constrained to exact 7.3.1 source hashes and applied only when relevant to your SPARQL-store setup. Keep the already required Wikibase/simple-cache and DataTransfer compatibility adjustments; do not infer that Composer repairs those automatically.

## Distribution files

GitHub/Composer archives exclude the inherited DemoData and Docker directories through .gitattributes. These are not part of the supported production install and can trigger legacy system-unzip filename handling on macOS. The install inventory is publication/DISTRIBUTION-SHA256SUMS; publication/SHA256SUMS describes the full source tree. The inventories do not hash themselves or each other.
