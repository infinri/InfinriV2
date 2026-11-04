<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="debug" :code="false">
			<k-text>
				<p>
					<code>window.panel.config.debug</code> returns the current debug mode.
					Use <code>window.panel.debug</code> as shortcut.
				</p>
				<k-code>{{ $panel.config.debug }}</k-code>
				<k-toggle-input
					:value="$panel.config.debug"
					@input="$panel.config.debug = $event"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="infinritext" :code="false">
			<k-text>
				<p>
					<code>window.panel.config.infinritext</code> If true, the textarea will
					insert code as infinritext instead of markdown.
				</p>
				<k-code>{{ $panel.config.infinritext }}</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
