import { describe, expect, it } from "vitest";
import { isObject } from "@/helpers/object";
import Buttons from "./Buttons.vue";
import RemoveDialog from "../RemoveDialog.vue";
import UploadDialog from "../UploadDialog.vue";

function context(props = {}, { isLoading = false, files = [] } = {}) {
	return {
		submitButton: true,
		cancelButton: true,
		...props,
		button: Buttons.methods.button,
		$emit: () => {},
		$helper: { object: { isObject } },
		$panel: {
			dialog: { isLoading },
			t: (key) => key,
			upload: { files }
		}
	};
}

describe("DialogButtons props", () => {
	it("only accepts the cancelButton and submitButton options", () => {
		expect(Object.keys(Buttons.mixins[0].props)).toStrictEqual([
			"cancelButton",
			"submitButton"
		]);
	});
});

describe("DialogButtons.submit", () => {
	const submit = Buttons.computed.submit;

	it("uses the defaults", () => {
		expect(submit.call(context())).toStrictEqual({
			class: "k-dialog-button-submit",
			icon: "check",
			text: "confirm",
			theme: "positive",
			type: "submit",
			variant: "filled"
		});
	});

	it("uses a string as text", () => {
		const button = submit.call(context({ submitButton: "Save" }));
		expect(button.text).toBe("Save");
		expect(button.icon).toBe("check");
		expect(button.theme).toBe("positive");
	});

	it("merges theme, icon and disabled from the submitButton object", () => {
		const button = submit.call(
			context({
				submitButton: {
					disabled: true,
					icon: "trash",
					text: "Delete",
					theme: "negative"
				}
			})
		);

		expect(button.disabled).toBe(true);
		expect(button.icon).toBe("trash");
		expect(button.text).toBe("Delete");
		expect(button.theme).toBe("negative");
	});

	it("shows the loader and disables the button while loading", () => {
		const button = submit.call(
			context(
				{ submitButton: { icon: "trash", disabled: false } },
				{ isLoading: true }
			)
		);

		expect(button.disabled).toBe(true);
		expect(button.icon).toBe("loader");
	});

	it("can be removed", () => {
		expect(submit.call(context({ submitButton: false }))).toBe(false);
	});
});

describe("RemoveDialog.removeButton", () => {
	const removeButton = RemoveDialog.computed.removeButton;

	it("defaults to a negative trash button", () => {
		expect(removeButton.call(context())).toStrictEqual({
			icon: "trash",
			text: "delete",
			theme: "negative"
		});
	});

	it("keeps icon and theme for a custom text", () => {
		expect(
			removeButton.call(context({ submitButton: "Delete all" }))
		).toStrictEqual({
			icon: "trash",
			text: "Delete all",
			theme: "negative"
		});
	});

	it("lets the submitButton object override icon and theme", () => {
		expect(
			removeButton.call(
				context({ submitButton: { icon: "undo", theme: "notice" } })
			)
		).toStrictEqual({
			icon: "undo",
			text: "delete",
			theme: "notice"
		});
	});

	it("can be removed", () => {
		expect(removeButton.call(context({ submitButton: false }))).toBe(false);
	});
});

describe("UploadDialog.uploadButton", () => {
	const uploadButton = UploadDialog.computed.uploadButton;
	const submitButton = { icon: "upload", text: "upload" };

	it("is disabled without files", () => {
		expect(uploadButton.call(context({ submitButton }))).toStrictEqual({
			icon: "upload",
			text: "upload",
			disabled: true
		});
	});

	it("is enabled with files", () => {
		const button = uploadButton.call(
			context({ submitButton }, { files: [{ id: "a" }] })
		);
		expect(button.disabled).toBe(false);
	});

	it("respects a disabled submitButton with files", () => {
		const button = uploadButton.call(
			context(
				{ submitButton: { ...submitButton, disabled: true } },
				{ files: [{ id: "a" }] }
			)
		);
		expect(button.disabled).toBe(true);
	});

	it("can be removed", () => {
		expect(uploadButton.call(context({ submitButton: false }))).toBe(false);
	});
});
