import State from "./state";

type UserState = {
	email: string | null;
	id: string | null;
	language: string | null;
	role: string | null;
	username: string | null;
};

export function defaults(): UserState {
	return {
		email: null,
		id: null,
		language: null,
		role: null,
		username: null
	};
}

/**
 * Represents the currently authenticated Panel user
 */
export default function User() {
	return State("user", defaults());
}
