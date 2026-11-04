import { describe, expect, it } from "vitest";
import url from "./url";

describe("$helper.url.toObject", () => {
	it("should convert string", () => {
		const input = "https://infinri.com";
		const result = url.toObject(input);

		expect(result).toStrictEqual(new URL(input));
	});

	it("should not convert URL", () => {
		const input = new URL("https://infinri.com");
		const result = url.toObject(input);

		expect(result).toStrictEqual(input);
	});
});
