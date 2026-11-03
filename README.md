# SemanticNeoWiki

A research-oriented, independent fork of [NeoWiki](https://github.com/ProfessionalWiki/NeoWiki): schema-based data entry, Wikidata and local Wikibase sources, optional Semantic MediaWiki annotations, knowledge graphs and AI access through MCP.

**Maintainer:** Klaus Feldmann / AufEinWort · **Contact:** [info@aufeinwort.info](mailto:info@aufeinwort.info) · **Research wiki:** [aufeinwort.info](https://aufeinwort.info)

**This fork is not maintained or supported by Jeroen De Dauw or Professional Wiki.** Please report fork-specific issues in [this repository](https://github.com/Klaus-Feldmann/SemanticNeoWiki/issues), not to the original NeoWiki project. Original authorship and licenses are retained.

## Version and supported environment

The candidate is **SemanticNeoWiki 0.1.9-test+2026-11-03**, with **NeoWikiBaseBridge 0.18.0-test+2026-11-03**. The complete candidate is published on `master` and the named test branch `semantic-0.1.9-test`. Use the named branch for the Composer test. It is based on NeoWiki 0.1.0; newer upstream development is not implicitly merged. The fork's initial upstream commit remains in Git history.

The preceding NeoWiki-named runtime (0.1.8-aewi / Bridge 0.17.0) runs on the production research server AufEinWort. The renamed candidate preserves its data model and features. **Composer packaging has been checked locally; installation from GitHub on that production server is the next live test.** Do not interpret the prior production use as a completed live test of the rename.

Support is limited to the tested setup: **MediaWiki 1.47.0-wmf.24, PHP 8.3.36, the legacy parser, UnlinkedWikibase 3.0.0, Semantic MediaWiki 7.3.1, Semantic Scribunto 3.0.0, local Wikibase, Scribunto and a Fuseki/SPARQL-backed Graph Explorer.** Earlier MediaWiki versions and other parser/backend combinations are not supported or tested by this fork. Later versions require their own verification. Additional reported versions are in [the environment snapshot](publication/TESTED-ENVIRONMENT.json).

## Added capabilities

- Source-qualified Wikidata/local Wikibase Q identities and schema-level P lookup by ID or label.
- Read-only source values with explicit own-value precedence and configurable local/source links.
- Historical BCE dates with precision/calendar information and year links.
- Images from local files, Commons or bound source properties, including Data/Graph thumbnails.
- Local page selection, structured source URLs and display names, passive Wikitext and centrally executed schema Wikitext.
- Optional schema-driven SMW annotations through Lua; relevant subject/schema saves trigger page processing rather than requiring an additional manual article save.
- Complete Data-view JSON, Turtle and TriG downloads and a separately supplied MCP read-tool patch.
- Readable select values, ordered fields, configured links and preferred descriptions in Graph Explorer.

External source changes alone do not guarantee immediate propagation to stored SMW annotations; affected pages must be processed. Existing SMW property types and research decisions remain under the administrator's control.

## Installation: Composer only

This candidate is for an existing MediaWiki server. **No Docker image, Docker demo or alternate application installer is supplied or supported.** Built Data UI/Graph assets are included; npm is not an installation requirement. Inherited demo data and Docker reference files remain in Git history/source browsing but are excluded from GitHub/Composer distribution archives.

In the MediaWiki root, merge the following into your existing `composer.local.json` without replacing other settings:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/Klaus-Feldmann/SemanticNeoWiki" }
  ],
  "require": {
    "aufeinwort/semantic-neowiki": "dev-semantic-0.1.9-test"
  },
  "config": {
    "allow-plugins": { "composer/installers": true }
  }
}
```

Use the MediaWiki root Composer workflow, which merges `composer.local.json`; do not run this local configuration file as a separate Composer project. An existing requirement for `neowiki/neowiki` must be removed for the switch. Remove an obsolete merge include of `extensions/NeoWiki/composer.json`, if present. Preserve existing compatibility work for Wikibase/DataTransfer; the fork does not automatically modify those packages.

Then, from the MediaWiki root:

```sh
composer update aufeinwort/semantic-neowiki --with-dependencies --no-dev --prefer-dist --no-scripts
```

The package installs to `extensions/SemanticNeoWiki`. It conflicts with a simultaneously installed official `neowiki/neowiki` package. Back up Composer files, existing extension files, LocalSettings and deployment-specific graph configuration before changing a working wiki. The [installation and migration guide](publication/INSTALLATION.md) covers the rest.

## LocalSettings.php and existing data

After the prerequisite extensions, use:

```php
wfLoadExtension( 'SemanticNeoWiki' );
wfLoadExtension( 'NeoWikiBaseBridge', "$IP/extensions/SemanticNeoWiki/NeoWikiBaseBridge/extension.json" );

$wgUseParsoidParser = false;
$wgUseParsoidLinksUpdate = false;
$wgNeoWikiAutoRenderMainSubject = false;
$wgNeoWikiSubjectFirst = false;

// Optional AufEinWort registration helper; replace the old include, do not duplicate it:
$wgAewiWikidataAutoNamespaces = [ 0 ];
$wgAewiWikidataSmwProperty = 'Wikidata';
require_once "$IP/extensions/SemanticNeoWiki/integrations/registration/AewiWikidataRegistrierung.php";
```

Remove the former `wfLoadExtension( 'NeoWiki' )` and old Bridge include for the switch; never load both implementations. Existing schema/subject content models, namespace IDs, `mw.neowiki`, `neowiki*` APIs, `NeoWiki*` settings, custom hooks and `MediaWiki:NeoWiki` are deliberately retained. **Do not rename stored data or these settings just because the extension folder changed.**

See the commented [LocalSettings example](publication/LocalSettings.SemanticNeoWiki.example.php), [graph configuration](graph-explorer/config.js), and [integration guide](publication/INSTALLATION.md). Keep the existing RDF base IRI and graph-store endpoints. The new graph page is under `/w/extensions/SemanticNeoWiki/graph-explorer/envendpoint.html` on a `/w` deployment; update old links/redirects deliberately. The SPARQL service remains external. The tested NeoWiki graph dataset (port 3331) and SMW SPARQLStore dataset (port 3030) are distinct; the example preserves that separation.

## Included integration material

- [SMW modules and templates](integrations/smw/README.md), with current wiki revision provenance. Their existing names remain NeoWiki; central executable schema Wikitext can continue invoking `{{NeoWiki}}`.
- [Wikidata registration helper](integrations/registration/README.md): AewiWikidataRegistrierung.php and its Magic Words file. Existing Q-registration templates can stay; the example helper's automatic title search uses German Wikipedia and is namespace-controlled.
- [Optional SMW 7.3.1 statistics correction](integrations/smw/compatibility/README.md): exact-source patch for SPARQLStore, with a checked backup/apply script. It is not applied by Composer and is not needed for every SMW setup.
- [MCP source/runtime patch](integrations/mcp/README.md): complete Data-view tool and recognition of the SemanticNeoWiki extension name. Apply before changing extension identity if MCP must retain the existing NeoWiki tools. It targets MCP 0.20.1, with checked compatible 0.20.0 runtime files; it contains no authentication data.

## Credits, license and verification

NeoWiki is the work of Professional Wiki, Jeroen De Dauw and its other contributors. Their credits and GPL-2.0-or-later license remain; see [COPYING](COPYING). Klaus Feldmann / AufEinWort supplied the research requirements, workflow/interface design and application testing. OpenAI ChatGPT/Codex assisted with implementation, tests and documentation. Publication does not imply endorsement by the original developers.

Zazuko Graph Explorer retains its LGPL notice, original sources and dependency notices; the separate MCP patch retains the MCP project's license. See [third-party provenance](publication/THIRD-PARTY.md).

[Verification and limits](publication/VERIFICATION.md) · [German overview](publication/LESEMICH.md) · [Contributing](CONTRIBUTING.md) · [Original NeoWiki documentation](README-UPSTREAM.md)
