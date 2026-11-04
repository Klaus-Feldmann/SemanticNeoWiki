# SemanticNeoWiki – Testfassung 0.1.10

Diese Fassung ergänzt die private Verarbeitung: SemanticNeoWiki 0.1.10-private-processing-test+2026-11-04 mit NeoWikiBaseBridge 0.18.1-private-processing-test+2026-11-04. Sie steht auf `master` und `semantic-0.1.10-test`. Der vorige Testzweig `semantic-0.1.9-test` bleibt unverändert erhalten.

Auf AufEinWort ist die Installation von SemanticNeoWiki über Composer bestätigt. Das neue Verarbeitungsmodul wurde anschließend als geprüfter Datei-Patch installiert. Bei gesperrtem anonymem Lesezugriff wurden eigene Werte, Änderungen und bewusstes Leeren über Hintergrundjobs geprüft. Die tatsächlichen SMW-Werte folgten; unabhängige händische Annotationen blieben erhalten.

Für den privaten Betrieb wird automatisch ein nicht anmeldbares technisches Konto mit ausschließlich Leserecht angelegt. Neue LocalSettings-Einstellungen oder Änderungen an Wikibase/UnlinkedWikibase sind dafür nicht erforderlich. **Der normale Jobdienst muss laufen.** Gespeicherte Daten werden darüber in den abgeleiteten Seitendaten und SMW-Annotationen nachgeführt. Ein zusätzliches manuelles Speichern des Artikeltexts entfällt.

Installation weiterhin ausschließlich über Composer mit `aufeinwort/semantic-neowiki:dev-semantic-0.1.10-test`. Die [Installationsanleitung](INSTALLATION.md) beschreibt auch die Aktualisierung von 0.1.9. Es gibt keinen SQL-Schemawechsel und keinen erforderlichen Gesamt-Rebuild. Die bestehenden Lua-Module, Vorlagen, Datenmodelle und Konfigurationsnamen bleiben erhalten. Der vorhandene MCP-Patch bleibt Bestandteil des Repositorys und ist für dieses Update unverändert.

Die [Prüfdokumentation](VERIFICATION.md) unterscheidet lokale Integrationsprüfungen, bestätigte Ergebnisse auf dem Produktionsserver und die jeweilige Composer-Verpackung. Die Veröffentlichung ist eine Testfassung für MediaWiki 1.47.0-wmf.24, PHP 8.3.36, SMW 7.4.0 und den dokumentierten Legacy-Parser-Aufbau.

Kontakt: Klaus Feldmann / AufEinWort, info@aufeinwort.info, https://aufeinwort.info. Dieser eigenständige Fork wird nicht von Jeroen De Dauw oder Professional Wiki betreut. Deren ursprüngliche Urheberschaft und Lizenzen bleiben erhalten.
