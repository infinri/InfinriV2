<template>
	<k-toolbar
		:buttons="buttons"
		data-inline="true"
		class="k-block-options"
		@mousedown.native.prevent
	/>
</template>

<script>
export const props = {
	props: {
		/**
		 * Block is slected together with other blocks
		 */
		isBatched: Boolean,
		/**
		 * No more blocks can be added
		 */
		isFull: Boolean,
		/**
		 * Block is displayed as hidden
		 */
		isHidden: Boolean,
		/**
		 * Block can be merged with all other selected blocks
		 */
		isMergable: Boolean
	}
};

/**
 * Floating options menu for a block that
 * appears when the block is focused/selected.
 *
 * @example <k-block-options :is-editable="true" />
 */
export default {
	mixins: [props],
	props: {
		/**
		 * Block can be edited
		 */
		isEditable: Boolean,
		/**
		 * Block can be split into multiple blocks
		 */
		isSplitable: Boolean
	},
	emits: [
		"chooseToAppend",
		"chooseToConvert",
		"chooseToPrepend",
		"copy",
		"duplicate",
		"hide",
		"merge",
		"open",
		"paste",
		"remove",
		"removeSelected",
		"show",
		"split",
		"sortDown",
		"sortUp"
	],
	computed: {
		buttons() {
			if (this.isBatched) {
				return [
					{
						icon: "template",
						title: this.$panel.t("copy"),
						click: () => this.$emit("copy")
					},
					{
						when: this.isMergable,
						icon: "merge",
						title: this.$panel.t("merge"),
						click: () => this.$emit("merge")
					},
					{
						icon: "trash",
						title: this.$panel.t("remove"),
						click: () => this.$emit("removeSelected")
					}
				];
			}

			return [
				{
					when: this.isEditable,
					icon: "edit",
					title: this.$panel.t("edit"),
					click: () => this.$emit("open")
				},
				{
					icon: "add",
					title: this.$panel.t("insert.after"),
					disabled: this.isFull,
					click: () => this.$emit("chooseToAppend")
				},
				{
					icon: "trash",
					title: this.$panel.t("delete"),
					click: () => this.$emit("remove")
				},
				{
					icon: "sort",
					title: this.$panel.t("sort.drag"),
					class: "k-sort-handle",
					key: (e) => this.sort(e)
				},
				{
					icon: "dots",
					title: this.$panel.t("more"),
					dropdown: [
						{
							icon: "angle-up",
							label: this.$panel.t("insert.before"),
							disabled: this.isFull,
							click: () => this.$emit("chooseToPrepend")
						},
						{
							icon: "angle-down",
							label: this.$panel.t("insert.after"),
							disabled: this.isFull,
							click: () => this.$emit("chooseToAppend")
						},
						"-",
						{
							when: this.isEditable,
							icon: "edit",
							label: this.$panel.t("edit"),
							click: () => this.$emit("open")
						},
						{
							icon: "refresh",
							label: this.$panel.t("field.blocks.changeType"),
							click: () => this.$emit("chooseToConvert")
						},
						{
							when: this.isSplitable,
							icon: "split",
							label: this.$panel.t("split"),
							click: () => this.$emit("split")
						},
						"-",
						{
							icon: "template",
							label: this.$panel.t("copy"),
							click: () => this.$emit("copy")
						},
						{
							icon: "download",
							label: this.$panel.t("paste.after"),
							disabled: this.isFull,
							click: () => this.$emit("paste")
						},
						"-",
						{
							icon: this.isHidden ? "preview" : "hidden",
							label: this.isHidden
								? this.$panel.t("show")
								: this.$panel.t("hide"),
							click: () => this.$emit(this.isHidden ? "show" : "hide")
						},
						{
							icon: "copy",
							label: this.$panel.t("duplicate"),
							click: () => this.$emit("duplicate")
						},
						"-",
						{
							icon: "trash",
							label: this.$panel.t("delete"),
							click: () => this.$emit("remove")
						}
					]
				}
			];
		}
	},
	methods: {
		open() {
			this.$refs.options.open();
		},
		sort(event) {
			switch (event.key) {
				case "ArrowUp":
					event.preventDefault();
					this.$emit("sortUp");
					break;
				case "ArrowDown":
					event.preventDefault();
					this.$emit("sortDown");
					break;
			}
		}
	}
};
</script>

<style>
.k-block-options {
	--toolbar-size: 30px;
	border: 1px solid light-dark(var(--color-border), var(--color-gray-900));
	box-shadow: var(--shadow-xl);
}
.k-block-options > .k-button:not(:last-of-type) {
	border-inline-end: 1px solid var(--toolbar-border);
}
.k-block-options .k-dropdown-content {
	margin-top: 0.5rem;
}
</style>
