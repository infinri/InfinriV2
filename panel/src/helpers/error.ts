/**
 * Whether the error has been caused by an aborted request
 */
export function isAbortError(error: unknown): error is Error {
	return error instanceof Error && error.name === "AbortError";
}
