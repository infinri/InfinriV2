<template>
	<k-view-button
		v-bind="$props"
		:disabled="disabled || $panel.content.isLocked()"
	/>
</template>

<script>
import Button from "./Button.vue";

/**
 * View header button to change the page status
 * @displayName StatusViewButton
 * @internal
 * @todo implement fully on backend when changes feature implemented
 */
export default {
	extends: Button
};
</script>
