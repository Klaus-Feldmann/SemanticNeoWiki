# Optional schema-driven Semantic MediaWiki annotations

Requires the accompanying SemanticNeoWiki/NeoWikiBaseBridge, Scribunto, Semantic MediaWiki and Semantic Scribunto. The shipped Lua modules/template names and documentation use German wiki namespace names (`Modul`, `Vorlage`) as in the tested wiki. An English-only installation must adapt module require names and page titles consistently; automated namespace localization is not claimed.

## Import wiki pages

`WIKISEITEN.json` maps each file under `Wikiseiten/` to its target page title and records the retrieved revision. These are current source snapshots, not a dump of research articles. Create/update only the listed pages:

- Modul:NeoWiki
- Modul:NeoWiki/API
- Modul:NeoWiki/config
- Vorlage:NeoWiki
- Vorlage:NeoWiki/URL-Zeile

The local config maps ordinary fields directly to SMW property names and includes examples for Werk (Leserschaft, Leserschaftziel, Werkausgabe). Review these local research naming choices. Captioned URL entries may select their caption as the property name and also emit associated subobjects.

Use `{{NeoWiki}}` on a hosting page. Alternatively add an executable Wikitext schema field containing `{{NeoWiki}}`; this enables central invocation without a template call in each article. Do not add both blindly. The page still needs its existing Q-registration mechanism for automatic external values.

## SMW property definitions

Reuse existing property pages/types. Define missing ones deliberately: Geburtsort/Sterbeort as Page, Quellenangabe or a source-specific caption property as URL, historical birth/death dates as Date. URL subobject property names come from the config. `property-examples.json` provides editable examples, not automatic changes to your wiki; `Vermerk` is AufEinWort's property namespace and is not assumed elsewhere. The module's field-name mapping does not choose an SMW datatype for you.

Own values precede external values, multi-values are retained and equal values deduplicated. Supported scalar/date fields are validated before #set. Image fields are skipped. Populated relation/multilingual/unknown structures report an unsupported-field error instead of inventing simplified annotations. Date uncertainty may require a local research value.

URL display text belongs to table/output formatting; storing a URL property does not make SMW's semantic browser display its separate label. The URL-Zeile template can render associated subobject attributes.

Subject saves and schema changes trigger/queue hosting-page processing through this extension. Keep the MediaWiki job runner operational. A plain browser reload does not guarantee a new parse or semantic-store update. External-source changes require affected-page processing under your local policy.

The local regression harness uses Lua stand-ins and public historical-person fixtures. It is not a replacement for checking a real saved subject and its semantic browsing result.
