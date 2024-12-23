<template>
	<cdx-field :status="status" :messages="twitterErrorMessage">
		<cdx-text-input
			:model-value="modelValue"
			@focus="isDirty = true"
			@update:model-value="$emit('update:modelValue', $event)"
			@blur="validateInput"
		></cdx-text-input>
		<template #label>
			{{ $i18n( "userprofilev2-twitter" ) }}
		</template>
		<template #help-text>
			{{ $i18n( "userprofilev2-twitter-help" ) }}
		</template>
	</cdx-field>
</template>

<script>

const { defineComponent, ref, computed, watch } = require( "vue" );
const { CdxField, CdxTextInput } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "TwitterField",
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
		const isDirty = ref( false );
		const twitterErrorMessage = ref( { error: "The Twitter username must contain an @." } );
		const regex = /@/;

		/**
		 * Validate the input and only fire the error message if the input is dirty
		 * A 'dirty' input is one that has been altered; this avoids firing the error if the
		 * input is empty/has not been changed.
		 */
		const validateInput = () => {
			if (isDirty.value && !props.modelValue.includes( "@" )) {
				twitterErrorMessage.value = { error: "The Twitter username must contain an @." };
			} else {
				twitterErrorMessage.value = { error: "" };
			}
		};

		const status = computed( () => {
			return isDirty.value && props.modelValue && !regex.test( props.modelValue )
				? "error"
				: "default";
		} );

		watch( () => props.modelValue, validateInput );

		return {
			status,
			twitterErrorMessage,
			isDirty,
			validateInput
		};
	}
} );

</script>

<style lang="less">

</style>
