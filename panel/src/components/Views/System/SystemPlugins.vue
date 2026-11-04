<template>
	<k-section
		v-if="plugins.length"
		:headline="$panel.t('plugins')"
		link="https://infinri.com/plugins"
	>
		<k-table
			:index="false"
			:columns="{
				name: {
					label: $panel.t('name'),
					type: 'url',
					mobile: true
				},
				author: {
					label: $panel.t('author')
				},
				version: {
					label: $panel.t('version'),
					type: 'update-status',
					mobile: true,
					width: '10rem'
				}
			}"
			:rows="plugins"
		/>
	</k-section>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		plugins: Array
	}
};
</script>
