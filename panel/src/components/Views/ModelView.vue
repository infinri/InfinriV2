<script>
/**
 * @internal
 */
export default {
	props: {
		api: String,
		blueprint: String,
		buttons: Array,
		id: String,
		link: String,
		lock: {
			type: [Boolean, Object]
		},
		next: Object,
		prev: Object,
		permissions: {
			type: Object,
			default: () => ({})
		},
		tab: {
			type: Object,
			default() {
				return {
					columns: []
				};
			}
		},
		tabs: {
			type: Array,
			default: () => []
		},
		uuid: String,
		versions: Object
	},
	data() {
		return {
			isSaved: true
		};
	},
	computed: {
		content() {
			return this.versions.changes;
		},
		diff() {
			return this.$panel.content.diff();
		},
		editor() {
			return this.lock.user.email;
		},
		hasDiff() {
			return this.$panel.content.hasDiff();
		},
		isLocked() {
			return this.lock.isLocked;
		},
		isSaving() {
			return this.$panel.content.isProcessing;
		},
		modified() {
			return this.lock.modified;
		}
	},
	watch: {
		api(newApi, oldApi) {
			// the api has changed when the editor navigated to a view
			// with the same view component, but a different model. In this
			// case, we need to unlock view for the old model.
			this.unlock(oldApi);
		}
	},
	mounted() {
		this.$panel.events.on("beforeunload", this.onBeforeUnload);
		this.$panel.events.on("content.save", this.onContentSave);
		this.$panel.events.on("keydown.left", this.toPrev);
		this.$panel.events.on("keydown.right", this.toNext);
		this.$panel.events.on("model.reload", this.$panel.reload);
		this.$panel.events.on("model.update", this.onModelUpdate);
		this.$panel.events.on("view.save", this.onViewSave);
	},
	destroyed() {
		this.$panel.events.off("beforeunload", this.onBeforeUnload);
		this.$panel.events.off("content.save", this.onContentSave);
		this.$panel.events.off("keydown.left", this.toPrev);
		this.$panel.events.off("keydown.right", this.toNext);
		this.$panel.events.off("model.reload", this.$panel.reload);
		this.$panel.events.off("model.update", this.onModelUpdate);
		this.$panel.events.off("view.save", this.onViewSave);

		// the view component gets discarded when a different view
		// is being opened, so let's unlock this view.
		if (this.isLocked === false) {
			this.unlock(this.api);
		}
	},
	methods: {
		onBeforeUnload(e) {
			if (this.$panel.content.isProcessing === true || this.isSaved === false) {
				e.preventDefault();
				e.returnValue = "";
			}

			// the window or tab is closed, so let's unlock this view.
			if (this.isLocked === false) {
				this.unlock(this.api);
			}
		},
		onContentSave({ api, language }) {
			if (api === this.api && language === this.$panel.language.code) {
				this.isSaved = true;
			}
		},
		async onDiscard() {
			try {
				await this.$panel.content.discard({
					api: this.api,
					language: this.$panel.language.code
				});

				this.isSaved = true;

				this.$panel.dialog.close();
				this.$panel.view.refresh();
			} catch (e) {
				this.$panel.error(e);
			}
		},
		onInput(values) {
			// the new values only exist in the form until the
			// throttled save request has written them
			this.isSaved = false;

			// update the content for the current view
			// this will also refresh the content prop
			this.$panel.content.updateLazy(values, {
				api: this.api,
				language: this.$panel.language.code
			});
		},
		onModelUpdate({ path } = {}) {
			if (path === this.api) {
				this.$panel.view.refresh();
			}
		},
		async onSubmit() {
			try {
				await this.$panel.content.publish(this.content, {
					api: this.api,
					language: this.$panel.language.code
				});

				this.isSaved = true;

				this.$panel.notification.success();
				this.$panel.events.emit("model.update");

				// the view needs to be refreshed to get an updated set of props
				// this will also rerender sections if needed
				await this.$panel.view.refresh();
			} catch (error) {
				this.$panel.notification.error(error);
			}
		},
		onViewSave(e) {
			e?.preventDefault?.();

			this.onSubmit();
		},
		toNext(e) {
			if (this.next && e.target.localName === "body") {
				this.$panel.view.open(this.next.link);
			}
		},
		toPrev(e) {
			if (this.prev && e.target.localName === "body") {
				this.$panel.view.open(this.prev.link);
			}
		},
		unlock(api) {
			this.$panel.content.unlockBeaconRequest({
				api: api,
				language: this.$panel.language.code
			});
		}
	}
};
</script>
