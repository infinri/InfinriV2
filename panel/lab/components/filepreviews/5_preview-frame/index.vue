<template>
	<k-lab-examples>
		<k-lab-example>
			<k-file-preview-frame> 👋 </k-file-preview-frame>
		</k-lab-example>
		<k-lab-example label="Options">
			<k-file-preview-frame :options="options"> 👋 </k-file-preview-frame>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		options() {
			return [{ text: "Website", icon: "open", link: "https://infinri.com" }];
		}
	}
};
</script>

<style>
.k-lab-example-canvas .k-file-preview-frame-column {
	max-height: 15rem;
}
</style>
