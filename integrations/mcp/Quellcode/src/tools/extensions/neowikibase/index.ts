import type { ExtensionPack } from '../types.ts';
import { neowikiGetDataView } from './neowikibase-get-data-view.ts';

/** Optional read model; ordinary NeoWiki installations keep their existing tool set. */
export const neowikiBasePack: ExtensionPack = {
	id: 'neowikibase',
	extensionNames: ['NeoWikiBaseBridge', 'SchemaBaseBridge'],
	tools: [neowikiGetDataView],
};
