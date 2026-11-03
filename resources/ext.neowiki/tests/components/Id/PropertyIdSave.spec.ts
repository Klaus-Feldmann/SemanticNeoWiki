import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, type VueWrapper } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { CdxCheckbox } from '@wikimedia/codex';
import PropertyDefinitionEditor from '@/components/SchemaEditor/PropertyDefinitionEditor.vue';
import PropertySourceEditor from '@/components/SchemaEditor/Property/PropertySourceEditor.vue';
import IdLookup from '@/components/Id/IdLookup.vue';
import { createTestWrapper, setupMwMock } from '../../VueTestHelpers';
import { newTextProperty } from '@/domain/propertyTypes/Text';
vi.mock( '@/persistence/PropertyIdLookup', () => ( { lookupPropertyIds: vi.fn().mockResolvedValue( [
	{ id: 'P31', label: 'ist ein(e)', description: 'Klasse', url: 'https://www.wikidata.org/wiki/Property:P31' },
] ) } ) );
function blocker( wrapper: VueWrapper ): string | null {
	return ( wrapper.vm as unknown as { unparseableInputMessage(): string | null } ).unparseableInputMessage();
}
describe( 'property ID selection through the schema field save check', () => {
	beforeEach( () => {
		setActivePinia( createPinia() );
		setupMwMock();
		vi.useFakeTimers();
	} );
	afterEach( () => vi.useRealTimers() );
	it( 'accepts a keyboard-confirmed selection all the way through the containing field', async () => {
		const w = createTestWrapper( PropertyDefinitionEditor, { property: newTextProperty( { name: 'Test' } ), otherPropertyNames: [] } );
		const source = w.findComponent( PropertySourceEditor );
		await source.findComponent( CdxCheckbox ).vm.$emit( 'update:modelValue', true );
		expect( blocker( w ) ).not.toBeNull();
		const picker = source.findComponent( IdLookup );
		const input = picker.get( 'input' );
		await input.trigger( 'focus' );
		await input.setValue( 'P31' );
		await vi.advanceTimersByTimeAsync( 301 );
		await flushPromises();
		await input.trigger( 'keydown', { key: 'ArrowDown' } );
		await input.trigger( 'keydown', { key: 'Enter' } );
		await flushPromises();
		expect( blocker( picker ) ).toBeNull();
		expect( blocker( source ) ).toBeNull();
		expect( blocker( w ) ).toBeNull();
		const emitted = w.emitted( 'update:property-definition' );
		expect( emitted?.slice( -1 )[ 0 ][ 0 ] ).toMatchObject( { externalProperty: { source: 'wikidata', id: 'P31' } } );
		await input.setValue( 'a different unconfirmed search' );
		expect( blocker( w ) ).not.toBeNull();
		w.unmount();
	} );
	it( 'allows saving an already stored reference when reopened', async () => {
		const w = createTestWrapper( PropertyDefinitionEditor, { property: {
			...newTextProperty( { name: 'Test' } ), externalProperty: { source: 'wikidata', id: 'P31' },
		}, otherPropertyNames: [] } );
		await flushPromises();
		expect( blocker( w.findComponent( IdLookup ) ) ).toBeNull();
		expect( blocker( w.findComponent( PropertySourceEditor ) ) ).toBeNull();
		expect( blocker( w ) ).toBeNull();
		w.unmount();
	} );
	it( 'retains a persisted binding when its lookup search is empty and link settings are changed', async () => {
		const w = createTestWrapper( PropertyDefinitionEditor, { property: {
			...newTextProperty( { name: 'Klassifizierung' } ), externalProperty: { source: 'wikidata', id: 'P31' },
		}, otherPropertyNames: [] } );
		await flushPromises();
		await w.findComponent( IdLookup ).get( 'input' ).setValue( '' );
		await w.findComponent( PropertySourceEditor ).vm.$emit( 'update:property', { wikiLink: { label: true, labelTarget: 'Vermerk:Klassifizierung' } } );
		await flushPromises();
		expect( blocker( w ) ).toBeNull();
		expect( w.emitted( 'update:property-definition' )?.slice( -1 )[ 0 ][ 0 ] ).toMatchObject( {
			externalProperty: { source: 'wikidata', id: 'P31' }, wikiLink: { labelTarget: 'Vermerk:Klassifizierung' },
		} );
		w.unmount();
	} );

} );
