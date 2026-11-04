import { describe, expect, it } from "vitest";
import { existsSync, readFileSync, readdirSync } from "node:fs";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";
import Vue from "vue";

const root = dirname(fileURLToPath(import.meta.url));

describe("removed panel APIs", () => {
	it("should only import the known modules in the entry file", () => {
		const entry = readFileSync(resolve(root, "index.js"), "utf8");
		const imports = [...entry.matchAll(/^import .*?["'](.+?)["'];$/gm)].map(
			(match) => match[1]
		);

		expect(imports).toStrictEqual([
			"vue",
			"./panel/app.js",
			"./components/index.js",
			"./config/errorhandling",
			"./helpers/index",
			"./config/i18n.js",
			"./libraries/index",
			"./panel/panel.js",
			"./styles/config.css",
			"./styles/reset.css",
			"./styles/utilities.css"
		]);
	});

	it("should not assign removed prototype aliases anywhere in panel sources", () => {
		const pattern = new RegExp(
			"prototype\\.\\$(t|api|dialog|drawer|dropdown|events|go|reload|url)\\s*=|panel\\.\\$t\\s*="
		);
		const offenders = [];
		const self = fileURLToPath(import.meta.url);

		const walk = (dir) => {
			for (const entry of readdirSync(dir, { withFileTypes: true })) {
				const path = resolve(dir, entry.name);

				if (entry.isDirectory()) {
					walk(path);
				} else if (/\.(js|ts|vue)$/.test(entry.name) && path !== self) {
					if (pattern.test(readFileSync(path, "utf8"))) {
						offenders.push(path.replace(root, "src"));
					}
				}
			}
		};

		walk(root);

		expect(offenders).toStrictEqual([]);
	});

	it("should not define the removed prototype aliases", () => {
		const aliases = [
			"$t",
			"$api",
			"$dialog",
			"$drawer",
			"$dropdown",
			"$events",
			"$go",
			"$reload",
			"$url"
		];

		for (const alias of aliases) {
			expect(Vue.prototype[alias], alias).toBeUndefined();
		}
	});

	it("should not use the removed prototype aliases anywhere in panel sources", () => {
		const pattern = new RegExp(
			"\\$(t|api|dialog|drawer|dropdown|events|go|reload|url)\\b[(.]|panel\\.\\$t\\b"
		);
		const offenders = [];
		const self = fileURLToPath(import.meta.url);

		const walk = (dir) => {
			for (const entry of readdirSync(dir, { withFileTypes: true })) {
				const path = resolve(dir, entry.name);

				if (entry.isDirectory()) {
					walk(path);
				} else if (/\.(js|ts|vue)$/.test(entry.name) && path !== self) {
					if (pattern.test(readFileSync(path, "utf8"))) {
						offenders.push(path.replace(root, "src"));
					}
				}
			}
		};

		walk(root);
		walk(resolve(root, "../lab"));

		expect(offenders).toStrictEqual([]);
	});

	it("should not ship deleted component files", () => {
		const files = [
			"components/Layout/Bubble.vue",
			"components/Layout/Bubbles.vue",
			"components/Forms/Previews/BubblesFieldPreview.vue",
			"components/Forms/Writer/Writer.vue"
		];

		for (const file of files) {
			expect(existsSync(resolve(root, file)), file).toStrictEqual(false);
		}
	});

	it("should not register deleted components or aliases", async () => {
		window.panel = window.panel ?? { plugins: {} };

		const { default: Components } = await import("./components/index.js");
		Vue.use(Components);

		const registered = Vue.options.components;

		for (const name of [
			"k-bubble",
			"k-bubbles",
			"k-bubbles-field-preview",
			"k-writer",
			"k-calendar",
			"k-times"
		]) {
			expect(registered[name], name).toBeUndefined();
		}

		expect(registered["k-calendar-input"]).toBeDefined();
		expect(registered["k-timeoptions-input"]).toBeDefined();
		expect(registered["k-writer-input"]).toBeDefined();
	});
});
