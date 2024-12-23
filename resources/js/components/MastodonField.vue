<template>
	<cdx-field :status="status" :messages="mastodonErrorMessage">
		<cdx-text-input
			v-model="mastodonInputValue"
			@focus="isDirty = true"
			@blur="validateInput"
		></cdx-text-input>
		<template #label>
			{{ $i18n( "userprofilev2-mastodon" ) }}
		</template>
		<template #help-text>
			{{ $i18n( "userprofilev2-mastodon-help" ) }}
		</template>
	</cdx-field>
</template>

<script>

const { defineComponent, ref, computed } = require( "vue" );
const { CdxField, CdxTextInput } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "MastodonField",
	components: {
		CdxField,
		CdxTextInput
	},
	setup() {
		const mastodonInputValue = ref( "" );
		const isDirty = ref( false );
		const mastodonErrorMessage = ref( { error: "The Mastodon username must contain an @." } );
		const regex = /@/;

		/**
		 * Validate the input and only fire the error message if the input is dirty
		 * A 'dirty' input is one that has been altered; this avoids firing the error if the
		 * input is empty/has not been changed.
		 */
		const validateInput = () => {
			if (isDirty.value) {
				if (!mastodonInputValue.value) {
					mastodonErrorMessage.value = { error: "" }; // Clear error if input is empty
				} else if (!regex.test( mastodonInputValue.value )) {
					mastodonErrorMessage.value = { error: "The Mastodon username must contain an @." };
				} else {
					mastodonErrorMessage.value = { error: "" }; // Clear error if input is valid
				}
			}
		};

		const status = computed( () => {
			return isDirty.value && mastodonErrorMessage.value.error ? "error" : "default";
		} );

		return {
			status,
			mastodonInputValue,
			mastodonErrorMessage,
			isDirty,
			validateInput
		};
	}
} );

</script>

<style lang="less">

</style>
