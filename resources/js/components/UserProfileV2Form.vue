<template>
	<about-me-field
		:model-value="userData['profile-aboutme'] || ''"
		@update:model-value="updateField('profile-aboutme', $event)"
	></about-me-field>
	<discord-field
		:model-value="userData['profile-discord']"
		@update:model-value="updateField('profile-discord', $event)"
	></discord-field>
	<twitter-field
		:model-value="userData['profile-twitter']"
		@update:model-value="updateField('profile-twitter', $event)"
	></twitter-field>
	<mastodon-field
		:model-value="userData['profile-mastodon']"
		@update:model-value="updateField('profile-mastodon', $event)"
	></mastodon-field>
	<global-edits-checkbox></global-edits-checkbox>
	<global-groups-checkbox></global-groups-checkbox>
	<file-input-widget :maxSize="5" accept="image/png, image/jpeg"></file-input-widget>
</template>

<script>
const { defineComponent, toRefs } = require( "vue" );
const aboutMeField = require( "./AboutMeField.vue" );
const discordField = require( "./DiscordField.vue" );
const twitterField = require( "./TwitterField.vue" );
const mastodonField = require( "./MastodonField.vue" );
const globalEditsCheckbox = require( "./GlobalEditsCheckbox.vue" );
const globalGroupsCheckbox = require( "./GlobalGroupsCheckbox.vue" );
const fileInputWidget = require( "./FileInputWidget.vue" );
const { CdxField, CdxTextInput, CdxTextArea } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "UserProfileV2Form",
	components: {
		CdxField,
		CdxTextInput,
		CdxTextArea,
		aboutMeField,
		discordField,
		twitterField,
		mastodonField,
		globalEditsCheckbox,
		globalGroupsCheckbox,
		fileInputWidget
	},
	props: {
		userData: {
			type: Object,
			required: true,
			default: () => ( {} )
		}
	},
	emits: [ "update:userData" ],
	setup( props, { emit } ) {
		const { userData } = toRefs( props );

		const updateField = ( field, value ) => {
			const updatedData = {
				...props.userData,
				[field]: value
			};
			emit( "update:userData", updatedData );
		};

		return {
			userData,
			updateField
		};
	}
} );
</script>
