<template>
	<about-me-field></about-me-field>
	<discord-field></discord-field>
	<twitter-field></twitter-field>
	<mastodon-field></mastodon-field>
	<global-edits-checkbox></global-edits-checkbox>
	<global-groups-checkbox></global-groups-checkbox>
	<file-input-widget :maxSize="5" accept="image/png, image/jpeg"></file-input-widget>
</template>

<script>
const { defineComponent, ref, computed } = require( "vue" );
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
		userData: { type: Object, required: true }
	}

} );
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';

.userprofilev2-about-me {
	display: flex;
	align-items: baseline;
	flex-direction: row;
	justify-content: space-between;
	gap: 10px;

	&--error &-character-counter {
		color: red;
	}
}
</style>
