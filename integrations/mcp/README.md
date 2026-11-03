# Complete Data-view MCP patch

Use README-JEROEN.md for the source contract and commands. This contribution targets official MediaWiki-MCP-Server 0.20.1 (commit 5b979b5a64f8dbb3596b95efc0c1d3f570505646). The checked runtime installer also accepts the byte-compatible 0.20.0 baseline. It adds neowikibase-get-data-view; it is not an entire MCP server distribution.

Source checkout: git apply --check, apply the Quellcode.patch, then npm ci --ignore-scripts, typecheck, test and build as documented. Installed npm package: run `python3 anwenden.py /path/to/node_modules/@professional-wiki/mediawiki-mcp-server` to check, then repeat with `--anwenden` to back up and apply. Unknown target hashes abort. Restart the MCP process/client afterwards.

The patch preserves existing authentication and configuration. It contains no account/token material and does not bundle AufEinWort's independently maintained private-wiki discovery adapter. The current combined workflow was tested with that existing adapter. Do not apply blindly to a later MCP release. The underlying project's license is preserved in LICENSE-UPSTREAM.

The patch also preserves the existing NeoWiki tool set after renaming the extension to SemanticNeoWiki; apply it before changing the wiki extension identity. The expanded runtime manifest covers four files.
