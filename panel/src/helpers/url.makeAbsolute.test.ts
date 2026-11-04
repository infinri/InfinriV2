import { describe, expect, it, vi } from "vitest";
import url from "./url";

describe("$helper.url.makeAbsolute", () => {
	it("should not touch absolute URLs", () => {
		const result = url.makeAbsolute("https://infinri.com");
		expect(result).toStrictEqual("https://infinri.com");
	});

	it("should make URLs absolute", () => {
		vi.stubGlobal("location", new URL("https://infinri.com"));

		const result = url.makeAbsolute("/foo");
		expect(result).toStrictEqual("https://infinri.com/foo");
	});
});
