# AufEinWort Wikidata registration helper

The two PHP files are the exact user-supplied working integration. Include AewiWikidataRegistrierung.php once from LocalSettings.php after installing UnlinkedWikibase; it registers the accompanying i18n.magic.php itself. The original header records the earlier development environment; the current combined runtime is described in the root README.

Syntax: `{{#aewiwikidata:Q-ID|Wikipedia title|automatic (1/0)|missing-link category}}`. Explicit Q-ID is validated. Automatic resolution searches the German Wikipedia sitelink (`dewiki`), defaults to namespace 0 and uses existing UnlinkedWikibase fetching/jobs. It does not write to Wikidata or local Wikibase. Configure AewiWikidataExplicitNamespaces and AewiWikidataAutoNamespaces locally. The registered page property is unlinkedwikibase_id, which the Bridge reads.

An optional additional SMW annotation uses `$wgAewiWikidataSmwProperty`, default Wikidata-ID; empty string disables that annotation. Keep the current property's existing type. The helper also links the displayed UnlinkedWikibase label to a local page if the corresponding message contains a valid local title. These are site integrations, not a general identity-matching service.

AufEinWort currently calls registration via its existing WikibaseQ/template/module workflow. Those unrelated display templates are not imported by this fork and need not be changed on the existing server. For a fresh wiki, the supplied minimal example can be imported as Vorlage:SemanticNeoWiki/Wikidata; it provides explicit/automatic registration without the site's separate Wikibase display modules. It is an example and does not assign research identities by guesswork.

In the current AufEinWort configuration this optional SMW property is explicitly set to `Wikidata`, overriding the helper default. The new LocalSettings example preserves that actual name. Replacing the old external include must not create a second loaded copy of the same global functions.
