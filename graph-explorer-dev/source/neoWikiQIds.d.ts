import type * as React from 'react';
export function createNeoWikiQIdRow(react: typeof React, options?: {origin?: string; load?: (iri: string) => Promise<unknown>}): React.ComponentType<{iri: string; onChange?: () => void}>;

export function createNeoWikiDescriptionRow(react: typeof React, options?: {origin?: string; load?: (iri: string) => Promise<unknown>}): React.ComponentType<{iri: string; onChange?: () => void}>;
