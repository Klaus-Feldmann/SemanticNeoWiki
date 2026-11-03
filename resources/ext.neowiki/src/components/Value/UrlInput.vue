<template>
	<CdxField
		:is-fieldset="true"
		:messages="messages"
		:status="violationStatus( messages )"
		:optional="property.required === false">
		<template #label>
			{{ label }}
			<CdxIcon
				v-if="property.description"
				v-tooltip="property.description"
				:icon="cdxIconInfo"
				size="small" />
		</template>
		<div
			v-for="( entry, index ) in rows"
			:key="index"
			class="ext-neowiki-url-entry">
			<CdxField>
				<template #label>
					{{ $i18n( 'neowiki-url-address' ).text() }}
				</template>
				<CdxTextInput :model-value="entry.url" @update:model-value="update( index, 'url', $event )" />
			</CdxField>
			<CdxField :optional="true">
				<template #label>
					{{ $i18n( 'neowiki-url-label' ).text() }}
				</template>
				<CdxTextInput :model-value="entry.label || ''" @update:model-value="update( index, 'label', $event )" />
			</CdxField>
			<CdxField :optional="true">
				<template #label>
					{{ $i18n( 'neowiki-url-caption' ).text() }}
				</template>
				<CdxTextInput :model-value="entry.caption || ''" @update:model-value="update( index, 'caption', $event )" />
			</CdxField>
			<CdxField :optional="true">
				<template #label>
					{{ $i18n( 'neowiki-url-caption-page' ).text() }}
				</template>
				<LocalPageLinkInput
					:model-value="entry.captionPage || ''"
					:input-label="$i18n( 'neowiki-url-caption-page' ).text()"
					@update:model-value="update( index, 'captionPage', $event )" />
			</CdxField>
			<CdxButton v-if="property.multiple" @click="remove( index )">
				{{ $i18n( 'neowiki-url-remove' ).text() }}
			</CdxButton>
		</div>
		<CdxButton v-if="property.multiple" @click="add">
			{{ $i18n( 'neowiki-url-add' ).text() }}
		</CdxButton>
	</CdxField>
</template>

<script setup lang="ts">
import { computed, ref, watch, toRef } from 'vue';
import { cdxIconInfo } from '@wikimedia/codex-icons';
import { CdxField, CdxTextInput, CdxButton, CdxIcon, type ValidationMessages } from '@wikimedia/codex';
import { newUrlValue, urlEntries, type UrlEntry, type Value } from '@/domain/Value';
import { UrlProperty, isValidUrlInput } from '@/domain/propertyTypes/Url';
import { wikiPageUrl } from '@/presentation/wikiLinks';
import LocalPageLinkInput from '@/components/common/LocalPageLinkInput.vue';
import { ValueInputProps, ValueInputEmits, ValueInputExposes } from '@/components/Value/ValueInputContract';
import { preferErrorViolation, useServerViolations, violationStatus } from '@/composables/useServerViolations';
const props = withDefaults( defineProps<ValueInputProps<UrlProperty>>(), { modelValue: undefined, label: '' } );
const emit = defineEmits<ValueInputEmits>();
const rows = ref<UrlEntry[]>( [] );
let lastGood: Value | undefined = props.modelValue;
let lastEmitted: Value | undefined;
let hasEmitted = false;
const { relevant, format, emitClears } = useServerViolations( toRef( props, 'property' ), toRef( props, 'serverViolations' ), emit );
watch( () => props.modelValue, ( value ) => {
	if ( hasEmitted && value === lastEmitted ) {
		return;
	}
	lastGood = value;
	const entries = urlEntries( value );
	rows.value = entries.length ? entries.map( ( entry ) => ( { ...entry } ) ) : [ { url: '' } ];
}, { immediate: true } );
const problem = computed( () => {
	for ( const entry of rows.value ) {
		if ( !entry.url.trim() && [ entry.label, entry.caption, entry.captionPage ].some( ( text ) => text?.trim() ) ) {
			return mw.msg( 'neowiki-url-needs-address' );
		}
		if ( entry.url.trim() && !isValidUrlInput( entry.url.trim() ) ) {
			return mw.msg( 'neowiki-url-invalid-address' );
		}
		if ( entry.captionPage?.trim() && !wikiPageUrl( entry.captionPage ) ) {
			return mw.msg( 'neowiki-url-invalid-page' );
		}
	}
	return null;
} );
const messages = computed<ValidationMessages>( () => {
	if ( problem.value ) {
		return { error: problem.value };
	}
	const server = preferErrorViolation( relevant() );
	return server ? { [ server.severity ]: format( server ) } : {};
} );
function changed(): void {
	emitClears( 'all' );
	if ( problem.value ) {
		return;
	}
	const value = newUrlValue( rows.value );
	lastGood = value.parts.length ? value : undefined;
	lastEmitted = lastGood;
	hasEmitted = true;
	emit( 'update:modelValue', lastGood );
}
function update( index: number, key: keyof UrlEntry, text: string | number ): void {
	rows.value[ index ][ key ] = String( text );
	changed();
}
function add(): void {
	rows.value.push( { url: '' } );
}
function remove( index: number ): void {
	rows.value.splice( index, 1 );
	changed();
}
defineExpose<ValueInputExposes>( { getCurrentValue: () => lastGood, unparseableInputMessage: () => problem.value } );
</script>

<style scoped>
.ext-neowiki-url-entry {
	margin-bottom: 1em;
	padding: 0.75em;
	border: 1px solid #a2a9b1;
}
</style>
