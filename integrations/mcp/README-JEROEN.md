# Complete NeoWiki Data view: optional MCP read tool

This patch was developed against ProfessionalWiki/MediaWiki-MCP-Server v0.20.1, commit 5b979b5a64f8dbb3596b95efc0c1d3f570505646. It adds `neowikibase-get-data-view` as an optional extension pack. It also recognizes SemanticNeoWiki as an alias for the existing NeoWiki tool pack. Existing tool APIs, authentication, configuration and dependencies remain unchanged.

## Required server-side API

NeoWikiBaseBridge (the accompanying adapted NeoWiki extension) must expose action=neowikibasedataview, read-model version 1. This API was implemented and live-tested separately. Stock NeoWiki alone does not expose it. The tool is advertised only for wikis reporting NeoWikiBaseBridge in siteinfo; an older Bridge with no compatible endpoint still needs upgrading. No installation of SMW is required for this read tool.

Request: pageid, optional subject and language, format=json, formatversion=2. Response key: neowikibasedataview. Its envelope contains version=1, readOnly=true, page.id and subjects[].id/fields[].name; all other attributes are preserved. The server-side read service authorizes the page, schema and local relation targets, reads current subjects, and assembles own and external values without writes. Effective-value precedence does not discard the other values. Source IDs and source URLs remain qualified independently of local display links.

## Apply to the source checkout

Place the patch outside or at the checkout root, then run from that checkout:

```sh
git apply --check /path/to/MediaWiki-MCP-0.20.1-NeoWiki-Datenansicht-Quellcode.patch
git apply /path/to/MediaWiki-MCP-0.20.1-NeoWiki-Datenansicht-Quellcode.patch
npm ci --ignore-scripts
npm run typecheck
npm test
npm run build
```

This is a standard unified diff; if the current development branch has moved, inspect/reconcile the two registration/test-list hunks and add the new tool, pack and test files. The executable patch can be applied with git apply; no custom installer is needed for the source contribution.

## Behavior

Inputs: exactly one of title/pageId; optional subject, language, exact fields. Resolves titles through the existing authenticated mwn context and invokes the read-only Action API through that same context. No forwarded custom Authorization header, anonymous fallback, source lookup implementation or SMW dependency is added.

Both machine-readable structuredContent and the normal server text formatter carry the selected response. Unknown fields are rejected. Fields retain schema order. Own/external/effective values, source IDs, descriptions, calendars, URL-entry metadata and error states are not flattened away. The response selection is explicit. complete is preserved from the upstream read service and must not be interpreted as completeness of a biography.

If the JSON payload or formatted text exceeds the existing contentMaxBytes budget, the tool returns an explicit error rather than silently truncating. Retry with a subject/field selection. A single oversized field currently needs a higher configured response budget; value-level pagination is not introduced by this patch. Field projection narrows the returned MCP payload, not the upstream read request.

An incompatible envelope or mismatched page/subject is rejected. Existing central authentication/error dispatch remains in use. readOnlyHint, destructiveHint, idempotentHint and openWorldHint describe the operation.

## Verification

Full project suite: 2,454 tests across 164 files passed, including 15 complete-view tool tests and a SemanticNeoWiki alias-gate regression. Typecheck and build passed; changed/new file formatting and scoped lint passed. Whole-project lint reported four pre-existing unsafe-assertion warnings outside this change.

A separate stdio MCP process running the patched official 0.20.1 distribution plus the independently maintained private-wiki discovery adapter negotiated a real client connection and advertised the new tool. It successfully read the live Cusanus, Goethe and Copernicus subjects, switched data language to English, projected exact fields, rejected an unknown field, and retained the old NeoWiki read tool. No wiki pages or research data were edited. OAuth refreshed through its existing normal flow.

No credentials or deployment configuration are included. The private-wiki discovery adapter is not part of this contribution. The runtime installer separately verifies both published 0.20.0/0.20.1 baselines and preserves that adapter. The official npm JavaScript files of those two versions were byte-identical.

## SemanticNeoWiki name

The normal NeoWiki tool pack accepts NeoWiki, SemanticNeoWiki and SemanticWikiSchema in siteinfo. Tool names, REST paths and stored data formats remain unchanged. The complete-view pack is gated on NeoWikiBaseBridge or SchemaBaseBridge. The new runtime patch therefore also changes dist/tools/extensions/neowiki/index.js. The installer safely accepts the earlier complete-view patch when the affected file hashes match; it then only changes files that still need updating.
