import { describe, expect, it } from "vitest";
import url from "./url";

describe("$helper.url.isAbsolute", () => {
	it("should work", () => {
		expect(url.isAbsolute("https://infinri.com")).toStrictEqual(true);
		expect(url.isAbsolute("http://infinri.com")).toStrictEqual(true);
		expect(url.isAbsolute("/foo")).toStrictEqual(false);
	});
});
