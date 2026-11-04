/**
 * Taken from: prosemirror-utils
 */
import { NodeSelection } from "prosemirror-state";

export default (selection) => {
	return selection instanceof NodeSelection;
};
