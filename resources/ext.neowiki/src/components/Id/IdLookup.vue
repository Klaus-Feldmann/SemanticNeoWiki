<template>
	<div>
		<CdxLookup
			v-model:input-value="inputText"
			:selected="unmatched ? null : selectedItem?.id ?? null"
			:menu-items="menuItems"
			:placeholder="$i18n( 'neowiki-id-placeholder' ).text()"
			:aria-label="$i18n( 'neowiki-id-property' ).text()"
			@update:selected="selectItem">
			<template #no-results>
				{{ $i18n( busy ? 'neowiki-id-loading' : failed ? 'neowiki-id-unavailable' : searched ? 'neowiki-id-no-results' : 'neowiki-id-placeholder' ).text() }}
			</template>
		</CdxLookup>
		<p v-if="selectedItem?.url && !unmatched">
			<a
				:href="selectedItem.url"
				:title="selectedItem.id"
				target="_blank"
				rel="noopener noreferrer">{{ selectedItem.label }}</a>
		</p>
		<p v-if="modelValue" class="ext-neowiki-id-saved">
			{{ $i18n( 'neowiki-id-saved' ).text() }} <code>{{ modelValue }}</code>
		</p>
		<CdxMessage
			v-if="failed"
			type="warning"
			inline>
			{{ $i18n( 'neowiki-id-unavailable' ).text() }}
		</CdxMessage>
		<CdxMessage
			v-else-if="unmatched && !busy"
			type="notice"
			inline>
			{{ $i18n( 'neowiki-id-confirm' ).text() }}
		</CdxMessage>
	</div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { CdxLookup, CdxMessage } from '@wikimedia/codex';
import { lookupPropertyIds, type IdSource, type IdSuggestion } from '@/persistence/PropertyIdLookup';

const props = defineProps<{ source: IdSource; modelValue: string | null }>();
const emit = defineEmits<{ 'update:modelValue': [ string | null ] }>();
const inputText = ref<string | number>( '' );
const selectedItem = ref<IdSuggestion | null>( null );
const selectedSource = ref<IdSource | null>( null );
const results = ref<IdSuggestion[]>( [] );
const busy = ref( false );
const failed = ref( false );
const searched = ref( false );
const unmatched = computed( () => String( inputText.value ).trim() !== '' &&
	String( inputText.value ) !== selectedItem.value?.label && String( inputText.value ) !== selectedItem.value?.id );
const menuItems = computed( () => ( selectedItem.value && !unmatched.value && !results.value.some( ( item ) => item.id === selectedItem.value?.id ) ?
	[ selectedItem.value, ...results.value ] : results.value ).map( ( item ) => ( {
	value: item.id, label: item.label, supportingText: item.id, description: item.description
} ) ) );
let sequence = 0;
let searching = false;
let resolving = false;
let timer: ReturnType<typeof setTimeout> | undefined;
let controller: AbortController | null = null;
function cancel(): number {
	clearTimeout( timer );
	searching = false;
	resolving = false;
	searched.value = false;
	controller?.abort();
	busy.value = false;
	return ++sequence;
}
onBeforeUnmount( cancel );

watch( () => [ props.source, props.modelValue ], async () => {
	const ticket = cancel();
	failed.value = false;
	results.value = [];
	if ( selectedSource.value === props.source && selectedItem.value?.id === props.modelValue ) {
		return;
	}
	// A persisted ID is already a confirmed selection. Label resolution enriches it;
	// it must not temporarily turn it into an empty Codex selection.
	selectedItem.value = props.modelValue ? { id: props.modelValue, label: props.modelValue, description: '', url: '' } : null;
	selectedSource.value = props.modelValue ? props.source : null;
	resolving = props.modelValue !== null;
	inputText.value = props.modelValue || '';
	if ( !props.modelValue ) {
		return;
	}
	controller = new AbortController();
	busy.value = true;
	try {
		const found = await lookupPropertyIds( props.source, props.modelValue, 'resolve', controller.signal );
		if ( ticket === sequence ) {
			resolving = false;
			selectedItem.value = found[ 0 ] ?? selectedItem.value;
			selectedSource.value = props.source;
			inputText.value = found[ 0 ]?.label ?? props.modelValue;
			failed.value = found.length === 0;
		}
	} catch {
		if ( ticket === sequence ) {
			resolving = false;
			failed.value = true;
		}
	} finally {
		if ( ticket === sequence ) {
			busy.value = false;
		}
	}
}, { immediate: true } );

watch( inputText, ( value ) => {
	const text = String( value ).trim();
	if ( text === selectedItem.value?.label || text === selectedItem.value?.id || ( resolving && text === props.modelValue ) ) {
		if ( searching ) {
			cancel();
			results.value = [];
		}
		return;
	}
	const ticket = cancel();
	results.value = [];
	failed.value = false;
	if ( text === '' ) {
		selectedItem.value = null;
		// Empty search text does not erase a stored reference. Disable the assignment explicitly to remove it.
		return;
	}
	busy.value = true;
	searching = true;
	timer = setTimeout( async () => {
		controller = new AbortController();
		try {
			const found = await lookupPropertyIds( props.source, text, 'search', controller.signal );
			if ( ticket === sequence ) {
				results.value = found;
				searched.value = true;
			}
		} catch {
			if ( ticket === sequence ) {
				failed.value = true;
			}
		} finally {
			if ( ticket === sequence ) {
				busy.value = false;
			}
		}
	}, 300 );
} );

function selectItem( id: string | number | null ): void {
	if ( id === null ) {
		return; // Codex clears its selection while typing; typing is not a confirmed replacement.
	}
	const item = results.value.find( ( result ) => result.id === id );
	if ( !item ) {
		return;
	}
	cancel();
	selectedItem.value = item;
	selectedSource.value = props.source;
	inputText.value = item.label;
	failed.value = false;
	emit( 'update:modelValue', item.id );
}
function unparseableInputMessage(): string | null {
	// An unchanged stored reference survives a temporary outage or a deleted remote property.
	const unchanged = props.modelValue !== null &&
		( String( inputText.value ).trim() === '' || String( inputText.value ) === props.modelValue || ( selectedItem.value?.id === props.modelValue && !unmatched.value ) );
	return unchanged ? null : mw.message( 'neowiki-id-confirm' ).text();
}
defineExpose( { unparseableInputMessage } );
</script>
