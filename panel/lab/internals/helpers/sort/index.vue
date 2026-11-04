<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="$helper.object.isEmpty()" :code="false">
			<k-text>
				<p>Natural sort algorithm with unicode support based on natsort:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.sort(options): boolean</k-code>
			</k-text>

			<h2>Example</h2>
			<k-code language="javascript"
				>const sorter = sort({ desc: direction === "desc", insensitive: true });
				array.sort((a, b) => sorter(a, b)});</k-code
			>
		</k-lab-example>
	</k-lab-examples>
</template>
