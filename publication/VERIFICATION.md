# Verification of the prepared test snapshot

## Repeated during publication preparation, 2026-11-03

- Data UI: 3,732 tests in 228 files passed against this publication copy.
- PHP schema/value/RDF/historical-date/read-model suites: 494 tests, 784 assertions passed using PHPUnit 9.6 and isolated test dependencies.
- Isolated REST/identity, page-field and executable-Wikitext behavior contracts passed. These use MediaWiki stand-ins, not a complete new MediaWiki installation.
- Current Lua module snapshots: 30 module/date checks, 202 schema-field checks and 24 structured-URL checks passed.
- Graph: 68 unit tests; DOM checks for identity, field order/links, page values, structured URLs, Wikitext, descriptions and own/remote images passed. Fresh dependency installation and guarded build reproduced the current deployed graph asset hashes; original public bundle exports were retained.
- MCP: source patch applied to the stated official 0.20.1 files and matched the supplied source files. Runtime installer dry-run, application and idempotent repeat passed against the official 0.20.1 npm distribution.
- Runtime extension source and Data UI JS/CSS remain the tested snapshot. Publication-only changes normalize dependency paths in the debug map, make test paths portable, refresh the graph dependency lockfile, retain only active graph bundles, add notices/docs/integrations, and archive upstream production-publishing workflows.

## Prior live evidence for the included runtime

The running AufEinWort installation reported NeoWiki 0.1.8-aewi+2026-11-03 and NeoWikiBaseBridge 0.17.0+2026-11-03. Authenticated complete JSON, Turtle and TriG downloads succeeded for Cusanus, Goethe and Copernicus. Complete JSON matched the MCP Data view excluding request timestamps; both RDF formats parsed and preserved the complete read snapshot and equivalent triples. Single-subject JSON succeeded for all three, and single-subject RDF was also checked for Goethe. Invalid format/language requests were rejected. These checks read data and did not edit research pages.

The user previously confirmed own/source value handling, page/URL/image fields, description precedence, schema Wikitext execution and automatic SMW annotation processing in the same environment. External-source propagation remains dependent on page reprocessing. The publication packaging has not been installed on a second clean wiki, and no new GitHub CI run is claimed.

## Reproduce locally

Use Node 24+, PHP 8.3+ and the project dependencies. For isolated PHP checks, install the requirements in `publication-tests/dependencies` and point `NEOWIKI_TEST_VENDOR` to that vendor/autoload.php. Run PHPUnit 9.6 with `-c publication-tests/phpunit.xml`; run each `publication-tests/*-contract.php` as a separate PHP process. The full original MediaWiki integration suite needs a surrounding MediaWiki installation.

In resources/ext.neowiki run `npm ci`, then `npm test -- --run`. The prepared JS/CSS are included; a new build uses `npm run build`.

In graph-explorer-dev run `npm ci --ignore-scripts`, `npm test`, `npm run build`, `node tests/compare-build.mjs`; run the `*dom*.mjs` scripts individually for the rendered-node checks. These are local test fixtures, not a browser session against production.

In integrations/smw/tests run `npm install --ignore-scripts` then `npm test`. NEOWIKI_LUA_RUNTIME may point at an existing fengari module. The supplied source/wiki naming is the German tested setup.

For the independent MCP source/runtime checks, follow integrations/mcp/README-JEROEN.md. Its earlier full-project typecheck/build and 2,453-test result is historical evidence for the unchanged patch, not a full MCP suite rerun during this packaging step.

## Renamed Composer candidate, 2026-11-03

SemanticNeoWiki 0.1.9-test / Bridge 0.18.0-test renames the registry, ResourceLoader install path, Composer identity, bridge dependency and maintenance extension checks. Existing persisted/data/API/config identifiers remain unchanged. The registration integration and SMW patch were supplied by the user; only whitelisted non-secret LocalSettings entries are published.

Repeated PHP 494/784 and Lua checks passed, as did REST/page/Wikitext contracts and a registry/asset/path rename contract. The updated MCP patch passed typecheck, build and 2,454 tests. Its installer was checked on the official distribution and the earlier complete-view-patched state. The SMW correction was compared with official 7.3.1 and tested for dry-run, backup application and idempotence.

A local Composer path-repository installation into an isolated root installed the package in extensions/SemanticNeoWiki and loaded its domain classes through Composer. This verifies packaging, not a MediaWiki server boot. GitHub retrieval and production migration are separate checks.
