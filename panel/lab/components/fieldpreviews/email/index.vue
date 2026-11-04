<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-email-field-preview value="mail@infinri.com" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Overflow">
			<k-lab-table-cell>
				<!-- @code -->
				<k-email-field-preview
					value="mail@infinriisasjkdhaksjhdakjshdkajhdhdakjshdkajhdhdakjshdkajhdhdakjshdkajhdhdakjshdkajhdhdakjshdkajhdhdakjshdkajhdkjahskjdhaksjhdakjshdkajsdhaksjhdkajshdkasjhdkjahsjkd.com"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-email-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
