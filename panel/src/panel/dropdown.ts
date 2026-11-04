import { reactive } from "vue";
import Feature, { defaults, type FeatureState } from "./feature";
import { type Listener } from "./listeners";

type DropdownState = FeatureState & {};

export default function Dropdown(panel: TODO) {
	const parent = Feature(panel, "dropdown", defaults() as DropdownState);

	return reactive({
		...parent,

		close(): void {
			this.emit("close");
			this.reset();
		},

		open(
			dropdown: string | URL | Partial<Prettify<DropdownState>>,
			options: Partial<Prettify<DropdownState>> | Listener = {}
		): Promise<Prettify<DropdownState>> {
			// prefix URLs
			if (typeof dropdown === "string") {
				dropdown = `/dropdowns/${dropdown}`;
			}

			return parent.open.call(this, dropdown, options);
		},

		/**
		 * Returns an async closure that loads the dropdown
		 * and passes its options to the given callback
		 */
		openAsync(
			dropdown: string,
			options: Partial<Prettify<DropdownState>> | Listener = {}
		): (ready: (items: unknown[]) => void) => Promise<void> {
			return async (ready: (items: unknown[]) => void) => {
				await this.open(dropdown, options);

				// load all options from the dropdown
				const items = this.options();

				// react to empty dropdowns
				if (items.length === 0) {
					throw Error(`The dropdown is empty`);
				}

				ready(items);
			};
		},

		options(): unknown[] {
			// return an empty array for invalid/non-existing options
			if (Array.isArray(this.props.options) === false) {
				return [];
			}

			return this.props.options;
		},

		set(state: Partial<Prettify<DropdownState>>): Prettify<DropdownState> {
			// dropdown responses only return the options
			// @ts-expect-error normalize the response
			if (state.options) {
				state.props = {
					// @ts-expect-error normalize malformed response
					options: state.options
				};
			}

			return parent.set.call(this, state);
		}
	});
}
