<template>
	<k-panel-outside class="k-installation-view">
		<div class="k-dialog k-installation-dialog">
			<k-dialog-body>
				<!-- installation complete -->
				<k-text v-if="isComplete">
					<k-headline>{{ $panel.t("installation.completed") }}</k-headline>
					<k-link to="/login">
						{{ $panel.t("login") }}
					</k-link>
				</k-text>

				<!-- ready to be installed -->
				<form v-else-if="isReady" @submit.prevent="install">
					<h1 class="sr-only">
						{{ $panel.t("installation") }}
					</h1>
					<k-fieldset :fields="fields" :value="user" @input="user = $event" />
					<k-button
						:text="$panel.t('install')"
						icon="check"
						size="lg"
						theme="positive"
						type="submit"
						variant="filled"
					/>
				</form>

				<!-- not meeting requirements -->
				<div v-else>
					<k-headline>
						{{ $panel.t("installation.issues.headline") }}
					</k-headline>

					<ul class="k-installation-issues" data-theme="negative">
						<li v-if="isInstallable === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.disabled')" />
						</li>

						<li v-if="requirements.php === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.php')" />
						</li>

						<li v-if="requirements.server === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.server')" />
						</li>

						<li v-if="requirements.mbstring === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.mbstring')" />
						</li>

						<li v-if="requirements.curl === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.curl')" />
						</li>

						<li v-if="requirements.accounts === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.accounts')" />
						</li>

						<li v-if="requirements.content === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.content')" />
						</li>

						<li v-if="requirements.media === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.media')" />
						</li>

						<li v-if="requirements.sessions === false">
							<k-icon type="alert" />
							<!-- eslint-disable-next-line vue/no-v-html -->
							<span v-html="$panel.t('installation.issues.sessions')" />
						</li>
					</ul>

					<k-button
						:text="$panel.t('retry')"
						icon="refresh"
						size="lg"
						theme="positive"
						variant="filled"
						@click="$panel.reload"
					/>
				</div>
			</k-dialog-body>
		</div>
	</k-panel-outside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		isInstallable: Boolean,
		isInstalled: Boolean,
		isOk: Boolean,
		requirements: Object,
		translations: Array
	},
	data() {
		return {
			user: {
				name: "",
				email: "",
				language: this.$panel.translation.code,
				password: "",
				role: "admin"
			}
		};
	},
	computed: {
		fields() {
			return {
				email: {
					label: this.$panel.t("email"),
					type: "email",
					link: false,
					autofocus: true,
					required: true
				},
				password: {
					label: this.$panel.t("password"),
					type: "password",
					placeholder: this.$panel.t("password") + " …",
					required: true
				},
				language: {
					label: this.$panel.t("language"),
					type: "select",
					options: this.translations,
					icon: "translate",
					empty: false,
					required: true
				}
			};
		},
		isReady() {
			return this.isOk && this.isInstallable;
		},
		isComplete() {
			return this.isOk && this.isInstalled;
		}
	},
	methods: {
		async install() {
			try {
				await this.$panel.api.system.install(this.user);
				await this.$panel.reload({
					globals: ["$system", "$translation"]
				});

				this.$panel.notification.success({
					message: this.$panel.t("welcome") + "!",
					icon: "smile"
				});
			} catch (error) {
				this.$panel.error(error);
			}
		}
	}
};
</script>

<style>
.k-installation-dialog {
	--dialog-color-back: light-dark(var(--color-white), var(--color-gray-950));
	--dialog-shadow: light-dark(var(--shadow), none);

	container-type: inline-size;
}
.k-installation-view .k-button {
	margin-top: var(--spacing-3);
	width: 100%;
}
.k-installation-view form .k-button {
	margin-top: 2.5rem;
}
.k-installation-view .k-headline {
	font-weight: var(--font-semi);
	margin-top: -0.5rem;
	margin-bottom: 0.75rem;
}
.k-installation-issues {
	line-height: 1.5em;
	font-size: var(--text-sm);
}
.k-installation-issues li {
	position: relative;
	padding: var(--spacing-6);
	background: var(--theme-color-back);
	color: var(--theme-color-text-highlight);
	padding-inline-start: 3.5rem;
	border-radius: var(--rounded);
}
.k-installation-issues .k-icon {
	position: absolute;
	top: calc(1.5rem + 2px);
	inset-inline-start: 1.5rem;
}
.k-installation-issues .k-icon {
	color: var(--theme-color-icon-highlight);
}
.k-installation-issues li:not(:last-child) {
	margin-bottom: 2px;
}
.k-installation-issues li code {
	font: inherit;
	color: var(--theme-color-icon-highlight);
}
</style>
