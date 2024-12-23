<template>
	<cdx-field :status="status" :messages="mastodonErrorMessage">
		<cdx-text-input
			:model-value="modelValue"
			@update:model-value="$emit('update:modelValue', $event)"
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
const { defineComponent, ref, computed, watch } = require( "vue" );
const { CdxField, CdxTextInput } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "MastodonField",
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
		const mastodonErrorMessage = ref( { error: "" } );
		const regex = /@/;

		const validateInput = () => {
			if (isDirty.value) {
				if (!props.modelValue) {
					mastodonErrorMessage.value = { error: "" };
				} else if (!regex.test( props.modelValue )) {
					mastodonErrorMessage.value = { error: "The Mastodon username must contain an @." };
				} else {
					mastodonErrorMessage.value = { error: "" };
				}
			}
		};

		const status = computed( () => {
			return isDirty.value && mastodonErrorMessage.value.error ? "error" : "default";
		} );

		// Add watch to validate when prop changes
		watch( () => props.modelValue, validateInput );

		return {
			status,
			mastodonErrorMessage,
			isDirty,
			validateInput
		};
	}
} );
</script>
