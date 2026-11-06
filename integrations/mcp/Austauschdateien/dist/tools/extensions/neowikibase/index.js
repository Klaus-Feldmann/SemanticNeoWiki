import { neowikiGetDataView } from './neowikibase-get-data-view.js';
/** Optional read model; ordinary NeoWiki installations keep their existing tool set. */
export const neowikiBasePack = {
    id: 'neowikibase',
    extensionNames: ['NeoWikiBaseBridge', 'SchemaBaseBridge'],
    tools: [neowikiGetDataView],
};
