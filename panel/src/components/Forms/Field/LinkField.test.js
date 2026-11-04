import { describe, expect, it } from "vitest";
import LinkField from "./LinkField.vue";

const { openedModel } = LinkField.computed;

function context(props) {
	return {
		$panel: {
			view: { props }
		}
	};
}

describe("LinkField.openedModel", () => {
	it("uses the top-level uuid of the current view", () => {
		const ctx = context({ id: "a/b", uuid: "page://abc" });
		expect(openedModel.call(ctx)).toBe("page://abc");
	});

	it("falls back to the top-level id without uuid", () => {
		const ctx = context({ id: "a/b", uuid: null });
		expect(openedModel.call(ctx)).toBe("a/b");
	});

	it("does not read a nested model prop", () => {
		const ctx = context({ model: { id: "x", uuid: "page://x" } });
		expect(openedModel.call(ctx)).toBeUndefined();
	});
});
