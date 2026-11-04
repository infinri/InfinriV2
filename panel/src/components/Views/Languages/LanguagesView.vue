<template>
	<k-panel-inside class="k-languages-view">
		<k-header>
			{{ $panel.t("view.languages") }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
			</template>
		</k-header>

		<template v-if="languages.length > 0">
			<k-section :headline="$panel.t('languages.default')">
				<k-collection :items="primaryLanguage" />
			</k-section>

			<k-section :headline="$panel.t('languages.secondary')">
				<k-collection
					v-if="secondaryLanguages.length"
					:items="secondaryLanguages"
				/>
				<k-empty
					v-else
					icon="translate"
					:disabled="!$panel.permissions.languages.create"
					@click="$panel.dialog.open('languages/create')"
				>
					{{ $panel.t("languages.secondary.empty") }}
				</k-empty>
			</k-section>
		</template>

		<template v-else-if="languages.length === 0">
			<k-empty
				icon="translate"
				:disabled="!$panel.permissions.languages.create"
				@click="$panel.dialog.open('languages/create')"
			>
				{{ $panel.t("languages.empty") }}
			</k-empty>
		</template>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		buttons: Array,
		languages: {
			type: Array,
			default: () => []
		},
		variables: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		languagesCollection() {
			return this.languages.map((language) => ({
				...language,
				image: {
					back: "black",
					color: "gray",
					icon: "translate"
				},
				link: () => {
					if (this.variables === false) {
						return null;
					}

					this.$panel.view.open(`languages/${language.id}`);
				},
				options: [
					{
						icon: "edit",
						text: this.$panel.t("edit"),
						disabled: this.variables === false,
						click: () => this.$panel.view.open(`languages/${language.id}`)
					},
					{
						icon: "cog",
						text: this.$panel.t("settings"),
						dialog: `languages/${language.id}/update`,
						disabled: !this.$panel.permissions.languages.update
					},
					{
						when: language.deletable,
						icon: "trash",
						text: this.$panel.t("delete"),
						dialog: `languages/${language.id}/delete`,
						disabled: !this.$panel.permissions.languages.delete
					}
				]
			}));
		},
		primaryLanguage() {
			return this.languagesCollection.filter((language) => language.default);
		},
		secondaryLanguages() {
			return this.languagesCollection.filter(
				(language) => language.default === false
			);
		}
	}
};
</script>
