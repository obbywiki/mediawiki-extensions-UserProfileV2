<template>
	<div class="userprofilev2-file-input">
		<div class="userprofilev2-file-upload__area">
			<input type="file" name="" id="" @change="handleFileChange($event)"/>
		</div>
	</div>
</template>

<script>
const {defineComponent, ref, computed} = require('vue');

module.exports = defineComponent({
	name: 'FileInputWidget',
	props: {
		maxSize: {
			type: Number,
			default: 5,
		},
		accept: {
			type: String,
			default: "image/png, image/jpeg",
		}
	},
	data() {
		return {
			isLoading: false,
			uploadReady: true,
			file: {
				name: "",
				size: 0,
				type: "",
				fileExtension: "",
				url: "",
				isImage: false,
				isUploaded: false
			}
		}
	},
	methods: {
		handleFileChange(e) {
			// Check if file is selected
			if (e.target.files && e.target.files[0]) {
				const file = e.target.files[0],
					fileSize = Math.round((file.size / 1024 / 1024) * 100) / 100,
					fileExtention = file.name.split(".").pop(),
					fileName = file.name.split(".").shift(),
					isImage = ["jpg", "jpeg", "png", "gif"].includes(fileExtention);
				// Print to console for now so we know what is going on
				console.log(fileSize, fileExtention, fileName, isImage);
			}
		},
	},
});
</script>

<style lang="less">
.userprofilev2-file-input {
	height: 100vh;
	width: 100%;
	display: flex;
	align-items: flex-start;
	justify-content: center;
}

.userprofilev2-file-upload__area {
	width: 600px;
	min-height: 200px;
	display: flex;
	align-items: center;
	justify-content: center;
	border: 2px dashed #ccc;
	margin-top: 40px;
}
</style>
