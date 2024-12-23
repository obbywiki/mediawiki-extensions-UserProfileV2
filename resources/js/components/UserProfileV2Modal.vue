<template>
	<cdx-dialog
		v-model:open="open"
		title="Save changes"
		:use-close-button="true"
		:primary-action="primaryAction"
		:default-action="defaultAction"
		@primary="onPrimaryAction"
		@default="open = false"
	>
		<user-profile-v2-form
			:user-data="userData"
			@update:user-data="userData = $event"
		></user-profile-v2-form>
	</cdx-dialog>
</template>

<script>
const UserProfileV2Form = require( "./UserProfileV2Form.vue" );

const { defineComponent, ref, onMounted } = require( "vue" );

const {
	CdxButton,
	CdxDialog
} = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "UserProfileV2Dialog",
	components: {
		CdxButton,
		CdxDialog,
		UserProfileV2Form
	},
	setup() {
		const open = ref( false );
		const userData = ref( null );

		const primaryAction = {
			label: "Save",
			actionType: "progressive"
		};

		const defaultAction = {
			label: "Cancel"
		};

		const button = document.getElementById( "userProfileV2-edit" );
		button.addEventListener( "click", function () {
			if (userData.value) {
				open.value = true;
			}
		} );

		const api = new mw.Api();

		// when the modal mounts to the DOM (on page load) send the API request to get the data we pass
		// to the form later on
		onMounted( () => {
			api.get( {
				action: "query",
				format: "json",
				list: "queryuserprofilev2",
				user_name: mw.config.get( "wgRelevantUserName" )
			} )
				.then( ( response ) => {
					userData.value = response.query?.[0] || {};
				} )
				.catch( ( error ) => {
					console.error( "API request failed:", error );
					userData.value = null;
				} );
		} );

		function onPrimaryAction() {
			open.value = false;
			// eslint-disable-next-line no-console
			console.log( "Primary action taken" );
		}

		return {
			open,
			primaryAction,
			defaultAction,
			onPrimaryAction,
			userData
		};
	}
} );
</script>

<style>

</style>
