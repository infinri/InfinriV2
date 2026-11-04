/**
 * Signals a network failure.
 * Handled by panel.error() which sets the panel's offline state.
 */
export default class OfflineError extends Error {
	request: Request;

	constructor(
		message: string,
		{
			request,
			cause
		}: {
			request: Request;
			cause?: unknown;
		}
	) {
		super(message, { cause });
		this.request = request;
	}
}
