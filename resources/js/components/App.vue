<template>
	<div class="userprofilev2-avatar-dialog">
		<user-profile-v2-modal></user-profile-v2-modal>
	</div>
</template>

<script>

module.exports = exports = {
	name: "UserProfileV2",
	components: {
		UserProfileV2Modal: require('./UserProfileV2Modal.vue')
	},
	data() {
		const button = document.getElementById('userProfileV2-edit')
		button.addEventListener('click', function () {
			console.log('Button, clicked!');
		});
	}
}
</script>
