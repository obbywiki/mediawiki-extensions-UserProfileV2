<template>
	<cdx-field :is-fieldset="true">
		<cdx-checkbox
			v-model="globalEditsInput"
		></cdx-checkbox>
		<template #label>
			{{ $i18n('userprofilev2-showglobaleditcount') }}
		</template>
		<template #help-text>
			{{ $i18n('userprofilev2-showglobaleditcounthelp') }}
		</template>
	</cdx-field>
</template>

<script>

const {defineComponent, ref, computed} = require('vue');
const {CdxField, CdxCheckbox} = require('@wikimedia/codex');

module.exports = defineComponent({
	name: 'GlobalEditsCheckbox',
	components: {
		CdxField,
		CdxCheckbox
	},
	setup() {
		const globalEditsInput = ref('');
		return {
			globalEditsInput,
		};
	}
});

</script>

<style lang="less">

</style>
