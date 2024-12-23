<template>
	<cdx-field
		:status="status"
		:messages="aboutMeErrorMessage">
		<cdx-text-area
			v-model="aboutMeValue"
		></cdx-text-area>
		<template #label>
			{{ $i18n( "userprofilev2-about-me" ) }}
		</template>
		<template #help-text>
			<div class="userprofilev2-about-me">
				<div class="userprofilev2-about-me-help-text">
					<p>{{ $i18n( "userprofilev2-about-me-help" ) }}</p>
				</div>
				<div class="userprofile-v2-about-me-character-counter">
					{{ charsRemaining }}
				</div>
			</div>
		</template>
	</cdx-field>
</template>

<script>
const { defineComponent, ref, computed } = require( "vue" );

const { CdxField, CdxTextInput, CdxTextArea } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "AboutMeField",
	components: {
		CdxField,
		CdxTextArea
	},
	setup() {

		const aboutMeValue = ref( "" );
		const MAX_ABOUT_ME_CHARS = 200;
		const charsRemaining = computed( () => MAX_ABOUT_ME_CHARS - aboutMeValue.value.length );
		const status = computed( () => charsRemaining.value < 0 ? "error" : "default" );
		const aboutMeErrorMessage = { error: "The about me section must be 200 characters or less." };

		return {
			status,
			charsRemaining,
			aboutMeValue,
			aboutMeErrorMessage
		};

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
