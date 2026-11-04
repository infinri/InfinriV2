<template>
	<k-button-group class="k-dialog-buttons">
		<k-button v-if="cancel" v-bind="cancel" />
		<k-button v-if="submit" v-bind="submit" />
	</k-button-group>
</template>

<script>
export const props = {
	props: {
		/**
		 * Options for the cancel button
		 */
		cancelButton: {
			default: true,
			type: [Boolean, String, Object]
		},
		/**
		 * Options for the submit button
		 */
		submitButton: {
			type: [Boolean, String, Object],
			default: true
		}
	}
};

/**
 * @displayName DialogButtons
 */
export default {
	mixins: [props],
	emits: ["cancel"],
	computed: {
		cancel() {
			return this.button(this.cancelButton, {
				click: () => this.$emit("cancel"),
				class: "k-dialog-button-cancel",
				icon: "cancel",
				text: this.$panel.t("cancel"),
				variant: "filled"
			});
		},
		submit() {
			const button = this.button(this.submitButton, {
				class: "k-dialog-button-submit",
				icon: "check",
				text: this.$panel.t("confirm"),
				theme: "positive",
				type: "submit",
				variant: "filled"
			});

			if (button === false) {
				return false;
			}

			if (this.$panel.dialog.isLoading) {
				return {
					...button,
					disabled: true,
					icon: "loader"
				};
			}

			return button;
		}
	},
	methods: {
		button(button, defaults) {
			if (typeof button === "string") {
				return {
					...defaults,
					text: button
				};
			}

			if (button === false) {
				return false;
			}

			if (this.$helper.object.isObject(button) === false) {
				return defaults;
			}

			return {
				...defaults,
				...button
			};
		}
	}
};
</script>

<style>
.k-button-group.k-dialog-buttons {
	display: grid;
	grid-template-columns: 1fr;
	gap: var(--spacing-3);
	--button-height: var(--height-lg);
}
.k-button-group.k-dialog-buttons:has(> .k-button:nth-child(2)) {
	grid-template-columns: 1fr 1fr;
}
</style>
