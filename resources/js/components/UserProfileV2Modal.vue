<template>
	<client-only>
		<cdx-dialog
			v-model:open="open"
			title="Save changes"
			:use-close-button="true"
			:primary-action="primaryAction"
			:default-action="defaultAction"
			@primary="onPrimaryAction"
			@default="open = false"
		>
			<user-profile-v2-form></user-profile-v2-form>
		</cdx-dialog>
	</client-only>
</template>

<script>
const UserProfileV2Form = require("./UserProfileV2Form.vue");

const {defineComponent, ref} = require('vue');

const {
	CdxButton,
	CdxDialog
} = require('@wikimedia/codex');

module.exports = defineComponent({
	name: 'UserProfileV2Dialog',
	components: {
		CdxButton,
		CdxDialog,
		UserProfileV2Form
	},
	setup() {
		const open = ref(false);

		const primaryAction = {
			label: 'Save',
			actionType: 'progressive'
		};

		const defaultAction = {
			label: 'Cancel'
		};

		button = document.getElementById('userProfileV2-edit');
		button.addEventListener('click', function () {
			open.value = true;
		})

		function onPrimaryAction() {
			open.value = false;
			// eslint-disable-next-line no-console
			console.log('Primary action taken');
		}

		return {
			open,
			primaryAction,
			defaultAction,
			onPrimaryAction
		};
	}
});
</script>

<style>

</style>
