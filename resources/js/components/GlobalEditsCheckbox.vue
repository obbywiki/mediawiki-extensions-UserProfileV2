<template>
	<cdx-field>
		<cdx-checkbox v-model="globalEditsInput">
			{{ $i18n( "userprofilev2-showglobaleditcount" ) }}
			<template #description>
				{{ $i18n( "userprofilev2-showglobaleditcounthelp" ) }}
			</template>
		</cdx-checkbox>
	</cdx-field>
</template>

<script>

const { defineComponent, ref, computed } = require( "vue" );
const { CdxField, CdxCheckbox } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "GlobalEditsCheckbox",
	components: {
		CdxField,
		CdxCheckbox
	},
	setup() {
		const globalEditsInput = ref( "" );
		return {
			globalEditsInput
		};
	}
} );

</script>

<style lang="less">

</style>
