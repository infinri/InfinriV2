<template>
	<k-string-input
		v-bind="$props"
		:spellcheck="false"
		autocomplete="off"
		class="k-search-input"
		type="search"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		// unset props
		autocomplete: null,
		pattern: null,
		spellcheck: null,

		placeholder: {
			default: () => window.panel.t("search") + " …",
			type: String
		}
	}
};

/**
 * @example <k-search-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [StringInput, props]
};
</script>
