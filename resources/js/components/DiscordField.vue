<template>
	<cdx-field :status="status" :messages="discordErrorMessage">
		<cdx-text-input
			:model-value="modelValue"
			@update:model-value="$emit('update:modelValue', $event)"
		></cdx-text-input>
		<template #label>
			{{ $i18n( "userprofilev2-discord" ) }}
		</template>
		<template #help-text>
			{{ $i18n( "userprofilev2-discord-help" ) }}
		</template>
	</cdx-field>
</template>

<script>

const { defineComponent, ref, computed, watch } = require( "vue" );
const { CdxField, CdxTextInput } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "DiscordField",
	components: {
		CdxField,
		CdxTextInput
	},
	props: {
		modelValue: {
			type: String,
			default: ""
		}
	},
	emits: [ "update:modelValue" ],
	setup( props ) {
		const discordErrorMessage = { error: "The Discord username must not contain a #." };
		const regex = /#/;

		const status = computed( () => regex.test( props.modelValue ) ? "error" : "default" );

		return {
			status,
			discordErrorMessage
		};

	}
} );

</script>

<style lang="less">
</style>
