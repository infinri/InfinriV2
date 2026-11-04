<template>
	<k-dialog
		ref="dialog"
		class="k-upload-dialog"
		v-bind="$props"
		:submit-button="uploadButton"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-dropzone @drop="$panel.upload.select($event)">
			<!-- No files yet -->
			<k-empty
				v-if="$panel.upload.files.length === 0"
				icon="upload"
				layout="cards"
				@click="$panel.upload.pick()"
			>
				{{ $panel.t("files.empty") }}
			</k-empty>

			<!-- Files list -->
			<k-upload-items
				v-else
				:items="$panel.upload.files"
				@remove="
					(file) => {
						$panel.upload.remove(file.id);
					}
				"
				@rename="
					(file, name) => {
						file.name = name;
					}
				"
			/>
		</k-dropzone>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	props: {
		submitButton: {
			type: [String, Boolean, Object],
			default: () => {
				return {
					icon: "upload",
					text: window.panel.t("upload")
				};
			}
		}
	},
	computed: {
		/**
		 * Submit button which stays disabled
		 * as long as no files are selected
		 */
		uploadButton() {
			if (this.submitButton === false) {
				return false;
			}

			let button = {};

			if (typeof this.submitButton === "string") {
				button = { text: this.submitButton };
			} else if (this.$helper.object.isObject(this.submitButton)) {
				button = this.submitButton;
			}

			return {
				...button,
				disabled:
					button.disabled === true || this.$panel.upload.files.length === 0
			};
		}
	}
};
</script>

<style>
.k-upload-dialog.k-dialog {
	--dialog-width: 40rem;
}
</style>
