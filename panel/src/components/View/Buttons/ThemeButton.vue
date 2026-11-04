<template>
	<k-view-button
		v-bind="$props"
		:icon="current === 'light' ? 'sun' : 'moon'"
		:options="options"
	/>
</template>

<script>
/**
 * View header button to toggle the Panel theme
 * @displayName ThemeViewButton
 * @unstable
 */
export default {
	props: {
		size: String,
		text: {
			type: String,
			default: () => window.panel.t("theme")
		},
		variant: String
	},
	computed: {
		current() {
			return this.$panel.theme.current;
		},
		options() {
			return [
				{
					text: this.$panel.t("theme.light"),
					icon: "sun",
					disabled: this.setting === "light",
					click: () => this.$panel.theme.set("light")
				},
				{
					text: this.$panel.t("theme.dark"),
					icon: "moon",
					disabled: this.setting === "dark",
					click: () => this.$panel.theme.set("dark")
				},
				{
					text: this.$panel.t("theme.automatic"),
					icon: "wand",
					disabled: this.setting === "system",
					click: () => this.$panel.theme.set("system")
				}
			];
		},
		setting() {
			return this.$panel.theme.setting ?? this.$panel.theme.config;
		}
	}
};
</script>
