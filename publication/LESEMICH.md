# SemanticNeoWiki – vorbereitete Veröffentlichung

Diese Testfassung enthält den zuletzt in AufEinWort bestätigten Stand: NeoWiki SemanticNeoWiki 0.1.9-test+2026-11-03 und NeoWikiBaseBridge 0.18.0-test+2026-11-03. Der technische Extension- und Ordnername ist SemanticNeoWiki. Bestehende Datenmodelle, API-/Lua-/Konfigurationsnamen bleiben erhalten. Installation ausschließlich über Composer.

- Vollständige Extension einschließlich gebauter Oberfläche und Graph Explorer.
- `integrations/smw/`: aktuelle Wikimodule, Vorlage, URL-Ausgabevorlage, lokale Namenskonfiguration und Prüfungen.
- `integrations/mcp/`: eigener Quellcode-/Laufzeitpatch für MediaWiki-MCP 0.20.1; geprüfter Installer auch für die kompatiblen Laufzeitdateien von 0.20.0.
- `publication/`: englische Installation, Funktionsumfang, Grenzen, Herkunft und Prüfstand.
- `publication-tests/`: lokal ausführbare PHP-Vertragsprüfungen mit relativen Pfaden.

Die Veröffentlichung beruht auf dem offiziellen 0.1.0-Release-Commit. Die vollständige geprüfte Fassung wird als neuer Commit auf master und unter semantic-0.1.9-test veröffentlicht. Der ursprüngliche Fork-Stand bleibt in der Git-Historie erhalten; neuere Originaländerungen werden nicht ungeprüft vermischt. Der Veröffentlichungsstatus wird im äußeren Prüfbericht festgehalten. Neue Änderungen aus dem aktuellen Originalprojekt sind nicht ungeprüft eingemischt.

Die [Installationsanleitung](INSTALLATION.md) enthält LocalSettings-Beispiele, die Einrichtung der optionalen SMW- und MCP-Bestandteile und die notwendigen lokalen Einstellungen. Für GitHub ist das Quellcodearchiv vorgesehen; für eine Wiki-Installation das gesonderte Paket mit dem Ordner `NeoWiki`.

Dies ist kein neues Funktionsupdate für die bereits laufende AufEinWort-Installation. Ein erneuter Austausch dort ist für die Veröffentlichung nicht erforderlich.

Kontakt: Klaus Feldmann / AufEinWort, info@aufeinwort.info, https://aufeinwort.info. Nicht von Jeroen De Dauw oder Professional Wiki betreut. Unterstützt wird nur die dokumentierte MediaWiki-1.47-Konfiguration; ältere Versionen werden nicht unterstützt.
