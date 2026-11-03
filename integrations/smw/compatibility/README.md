# Optional SMW 7.3.1 statistics correction

This exact-file correction applies to Semantic MediaWiki 7.3.1 `src/Maintenance/PropertyStatisticsRebuilder.php`. When the configured store is SPARQLStore, its SQL base store supplies data-item handlers used during statistics rebuilding. A SQLStore remains unchanged. It is independent of SemanticNeoWiki's field parsing and is not installed automatically by Composer.

The supplied replacement is the file used in AufEinWort. BASELINE.json identifies the official source and both hashes. To check, run `python3 anwenden.py /path/to/SemanticMediaWiki/src/Maintenance/PropertyStatisticsRebuilder.php`. Add `--anwenden` only when the exact match is confirmed and this correction is needed. The installer backs up the file, rejects other versions and is idempotent. An SMW upgrade may supersede or overwrite the correction. Keep original SMW license/author credits.

Local publication checks verify syntax, the exact source diff and installer behavior; they do not rebuild a production SMW statistics table. No statistics rebuild is required merely to install SemanticNeoWiki.
