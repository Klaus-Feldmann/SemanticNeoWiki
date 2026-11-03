<template>
	<div v-if="groups.length" class="ext-neowiki-entity-descriptions">
		<p
			v-for="( group, index ) in groups"
			:key="index"
			class="ext-neowiki-entity-descriptions__entry">
			<span :lang="group.language">{{ group.text }}</span>
			<small class="ext-neowiki-entity-descriptions__sources">{{ group.sources.map( item => $i18n( 'neowiki-auto-identity-' + item.source ).text() ).join( ' · ' ) }}</small>
		</p>
	</div>
</template>

<script setup lang="ts">
import type { EntityDescription } from '@/presentation/entityDescriptions';
defineProps<{ groups: EntityDescription[] }>();
</script>

<style scoped>
.ext-neowiki-entity-descriptions {
	overflow-wrap: anywhere;
	min-width: 0;
}

.ext-neowiki-entity-descriptions__entry {
	margin: 0 0 0.5em;
}

.ext-neowiki-entity-descriptions__sources {
	display: block;
	opacity: 0.75;
	font-size: 0.8em;
}
</style>
