<template>
	<cdx-field>
		<cdx-checkbox v-model="globalGroupsInput">
			{{ $i18n( "userprofilev2-showglobalgroups" ) }}
			<template #description>
				{{ $i18n( "userprofilev2-showglobalgroupshelp" ) }}
			</template>
		</cdx-checkbox>
	</cdx-field>
</template>

<script>

const { defineComponent, ref, computed } = require( "vue" );
const { CdxField, CdxCheckbox } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "GlobalGroupsCheckbox",
	components: {
		CdxField,
		CdxCheckbox
	},
	setup() {
		const globalGroupsInput = ref( "" );
		return {
			globalGroupsInput
		};
	}
} );

</script>

<style lang="less">

</style>
