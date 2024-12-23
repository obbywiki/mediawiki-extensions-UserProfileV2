<template>
	<cdx-field :status="status" :messages="twitterErrorMessage">
		<cdx-text-input
			v-model="twitterInputValue"
			@focus="isDirty = true"
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

const { defineComponent, ref, computed } = require( "vue" );
const { CdxField, CdxTextInput } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "TwitterField",
	components: {
		CdxField,
		CdxTextInput
	},
	setup() {
		const twitterInputValue = ref( "" );
		const isDirty = ref( false );
		const twitterErrorMessage = ref( { error: "The Twitter username must contain an @." } );
		const regex = /@/;

		/**
		 * Validate the input and only fire the error message if the input is dirty
		 * A 'dirty' input is one that has been altered; this avoids firing the error if the
		 * input is empty/has not been changed.
		 */
		const validateInput = () => {
			if (isDirty.value && !twitterInputValue.value.includes( "@" )) {
				twitterErrorMessage.value = { error: "The Twitter username must contain an @." };
			} else {
				twitterErrorMessage.value = { error: "" };
			}
		};

		const status = computed( () => {
			return isDirty.value && twitterInputValue.value && !regex.test( twitterInputValue.value )
				? "error"
				: "default";
		} );

		return {
			status,
			twitterInputValue,
			twitterErrorMessage,
			isDirty,
			validateInput
		};
	}
} );

</script>

<style lang="less">

</style>
