<template>
	<cdx-field
		:status="status"
		:messages="aboutMeErrorMessage">
		<cdx-text-area
			:model-value="modelValue"
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
const { defineComponent, computed } = require( "vue" );

const { CdxField, CdxTextArea } = require( "@wikimedia/codex" );

module.exports = defineComponent( {
	name: "AboutMeField",
	components: {
		CdxField,
		CdxTextArea
	},
	props: {
		modelValue: {
			type: String,
			default: ""
		}
	},
	emits: [ "update:modelValue" ],
	setup( props ) {
		const MAX_ABOUT_ME_CHARS = 200;
		const charsRemaining = computed( () => MAX_ABOUT_ME_CHARS - props.modelValue.length );
		const status = computed( () => charsRemaining.value < 0 ? "error" : "default" );
		const aboutMeErrorMessage = { error: "The about me section must be 200 characters or less." };

		return {
			status,
			charsRemaining,
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
