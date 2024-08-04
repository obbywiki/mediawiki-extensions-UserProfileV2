<template>
	<cdx-field :is-fieldset="true" :status="status" :messages="discordErrorMessage">
		<cdx-text-input
			v-model="discordInputValue"
		></cdx-text-input>
		<template #label>
			{{ $i18n('userprofilev2-discord') }}
		</template>
		<template #help-text>
			{{ $i18n('userprofilev2-discord-help') }}
		</template>
	</cdx-field>
</template>

<script>

const {defineComponent, ref, computed} = require('vue');
const {CdxField, CdxTextInput} = require('@wikimedia/codex');

module.exports = defineComponent({
	name: 'DiscordField',
	components: {
		CdxField,
		CdxTextInput,
	},
	setup() {

		const discordInputValue = ref('');
		const discordErrorMessage = {error: 'The Discord username must not contain a #.'};
		const regex = /#/;

		const status = computed(() => regex.test(discordInputValue.value) ? 'error' : 'default');

		return {
			status,
			discordInputValue,
			discordErrorMessage
		}

	}
});

</script>

<style lang="less">
</style>
