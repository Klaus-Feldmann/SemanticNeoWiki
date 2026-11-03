import { neowikiListSchemas } from './neowiki-list-schemas.js';
import { neowikiGetSchema } from './neowiki-get-schema.js';
import { neowikiCypherQuery } from './neowiki-cypher-query.js';
import { neowikiSearchSubjects } from './neowiki-search-subjects.js';
import { neowikiGetSubject } from './neowiki-get-subject.js';
import { neowikiGetPageSubjects } from './neowiki-get-page-subjects.js';
import { neowikiCreateSubject } from './neowiki-create-subject.js';
import { neowikiUpdateSubject } from './neowiki-update-subject.js';
import { neowikiDeleteSubject } from './neowiki-delete-subject.js';
import { neowikiSetMainSubject } from './neowiki-set-main-subject.js';
import { neowikiValidateSubject } from './neowiki-validate-subject.js';
import { neowikiMintSubjectIds } from './neowiki-mint-subject-ids.js';
export const neowikiPack = {
    id: 'neowiki',
    extensionNames: ['NeoWiki', 'SemanticNeoWiki'],
    tools: [
        neowikiListSchemas,
        neowikiGetSchema,
        neowikiCypherQuery,
        neowikiSearchSubjects,
        neowikiGetSubject,
        neowikiGetPageSubjects,
        neowikiCreateSubject,
        neowikiUpdateSubject,
        neowikiDeleteSubject,
        neowikiSetMainSubject,
        neowikiValidateSubject,
        neowikiMintSubjectIds,
    ],
};
