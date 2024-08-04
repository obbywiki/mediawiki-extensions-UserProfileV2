<template>
	<cdx-field :is-fieldset="true">
		<cdx-checkbox
			v-model="globalGroupsInput"
		></cdx-checkbox>
		<template #label>
			{{ $i18n('userprofilev2-showglobalgroups') }}
		</template>
		<template #help-text>
			{{ $i18n('userprofilev2-showglobalgroupshelp') }}
		</template>
	</cdx-field>
</template>

<script>

const {defineComponent, ref, computed} = require('vue');
const {CdxField, CdxCheckbox} = require('@wikimedia/codex');

module.exports = defineComponent({
	name: 'GlobalGroupsCheckbox',
	components: {
		CdxField,
		CdxCheckbox
	},
	setup() {
		const globalGroupsInput = ref('');
		return {
			globalGroupsInput,
		};
	}
});

</script>

<style lang="less">

</style>
