<script>
import TagsFieldPreview from "./TagsFieldPreview.vue";

export default {
	extends: TagsFieldPreview,
	inheritAttrs: false,
	class: "k-array-field-preview",
	computed: {
		tags() {
			return [
				{
					text:
						this.value.length === 1
							? `1 ${this.$panel.t("entry")}`
							: `${this.value.length} ${this.$panel.t("entries")}`
				}
			];
		}
	}
};
</script>
