<template>
	<k-text-dialog
		ref="dialog"
		v-bind="$props"
		:submit-button="removeButton"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<slot />
	</k-text-dialog>
</template>

<script>
import TextDialog from "./TextDialog.vue";

export default {
	mixins: [TextDialog],
	computed: {
		/**
		 * Submit button with the trash icon and negative theme
		 * as defaults, which the `submitButton` prop can override
		 */
		removeButton() {
			if (this.submitButton === false) {
				return false;
			}

			const defaults = {
				icon: "trash",
				text: this.$panel.t("delete"),
				theme: "negative"
			};

			if (typeof this.submitButton === "string") {
				return { ...defaults, text: this.submitButton };
			}

			if (this.$helper.object.isObject(this.submitButton)) {
				return { ...defaults, ...this.submitButton };
			}

			return defaults;
		}
	}
};
</script>
