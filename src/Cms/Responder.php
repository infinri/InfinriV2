<?php

namespace Infinri\Cms;

use Infinri\Exception\InvalidArgumentException;
use Infinri\Filesystem\Mime;
use Infinri\Http\Response as HttpResponse;
use Infinri\Http\VolatileHeaders;
use Infinri\Toolkit\Str;
use Stringable;

class Responder implements Stringable
{
	/**
	 * Timestamp when the response expires
	 * in Infinri's cache
	 */
	protected int|null $expires = null;

	/**
	 * HTTP status code
	 */
	protected int|null $code = null;

	/**
	 * Response body
	 */
	protected string|null $body = null;

	/**
	 * Flag that defines whether the current
	 * response can be cached by Infinri's cache
	 */
	protected bool $cache = true;

	/**
	 * HTTP headers
	 */
	protected array $headers = [];

	/**
	 * Content type
	 */
	protected string|null $type = null;

	/**
	 * Flag that defines whether the current
	 * response uses the HTTP `Authorization`
	 * request header
	 */
	protected bool $usesAuth = false;

	/**
	 * List of cookie names the response
	 * relies on
	 */
	protected array $usesCookies = [];

	/**
	 * Volatile headers manager
	 */
	protected VolatileHeaders|null $volatileHeaders = null;

	/**
	 * Creates and sends the response
	 */
	public function __toString(): string
	{
		return (string)$this->send();
	}

	/**
	 * Setter and getter for the response body
	 *
	 * @return $this|string|null
	 */
	public function body(string|null $body = null): static|string|null
	{
		if ($body === null) {
			return $this->body;
		}

		$this->body = $body;
		return $this;
	}

	/**
	 * Setter and getter for the flag that defines
	 * whether the current response can be cached
	 * by Infinri's cache
	 *
	 * @return bool|$this
	 */
	public function cache(bool|null $cache = null): bool|static
	{
		if ($cache === null) {
			// never ever cache private responses
			if (static::isPrivate($this->usesAuth(), $this->usesCookies()) === true) {
				return false;
			}

			return $this->cache;
		}

		$this->cache = $cache;
		return $this;
	}

	/**
	 * Setter and getter for the flag that defines
	 * whether the current response uses the HTTP
	 * `Authorization` request header
	 *
	 * @return bool|$this
	 */
	public function usesAuth(bool|null $usesAuth = null): bool|static
	{
		if ($usesAuth === null) {
			return $this->usesAuth;
		}

		$this->usesAuth = $usesAuth;
		return $this;
	}

	/**
	 * Setter for a cookie name that is
	 * used by the response
	 */
	public function usesCookie(string $name): void
	{
		// only add unique names
		if (in_array($name, $this->usesCookies, true) === false) {
			$this->usesCookies[] = $name;
		}
	}

	/**
	 * Setter and getter for the list of cookie
	 * names the response relies on
	 *
	 * @return array|$this
	 */
	public function usesCookies(array|null $usesCookies = null): array|static
	{
		if ($usesCookies === null) {
			return $this->usesCookies;
		}

		$this->usesCookies = $usesCookies;
		return $this;
	}

	/**
	 * Setter and getter for the cache expiry
	 * timestamp for Infinri's cache
	 *
	 * @param int|string|null $expires Timestamp, number of minutes or time string to parse
	 * @param bool $override If `true`, the already defined timestamp will be overridden
	 * @return int|null|$this
	 */
	public function expires($expires = null, bool $override = false): static|int|null
	{
		// getter
		if ($expires === null && $override === false) {
			return $this->expires;
		}

		// explicit un-setter
		if ($expires === null) {
			$this->expires = null;
			return $this;
		}

		// normalize the value to an integer timestamp
		if (is_int($expires) === true && $expires < 1000000000) {
			// number of minutes
			$expires = time() + ($expires * 60);
		} elseif (is_int($expires) !== true) {
			// time string
			$parsedExpires = strtotime($expires);

			if (is_int($parsedExpires) !== true) {
				throw new InvalidArgumentException(
					message: 'Invalid time string "' . $expires . '"'
				);
			}

			$expires = $parsedExpires;
		}

		// by default only ever *reduce* the cache expiry time
		if (
			$override === true ||
			$this->expires === null ||
			$expires < $this->expires
		) {
			$this->expires = $expires;
		}

		return $this;
	}

	/**
	 * Setter and getter for the status code
	 *
	 * @return int|null|$this
	 */
	public function code(int|null $code = null): static|int|null
	{
		if ($code === null) {
			return $this->code;
		}

		$this->code = $code;
		return $this;
	}

	/**
	 * Construct response from an array
	 */
	public function fromArray(array $response): void
	{
		$this->body($response['body'] ?? null);
		$this->cache($response['cache'] ?? null);
		$this->code($response['code'] ?? null);
		$this->expires($response['expires'] ?? null);
		$this->headers($response['headers'] ?? null);
		$this->type($response['type'] ?? null);
		$this->usesAuth($response['usesAuth'] ?? null);
		$this->usesCookies($response['usesCookies'] ?? null);
		$this->volatileHeaders($response['volatileHeaders'] ?? null);
	}

	/**
	 * Setter and getter for a single header
	 *
	 * @param string|false|null $value
	 * @param bool $lazy If `true`, an existing header value is not overridden
	 * @return string|null|$this
	 */
	public function header(string $key, $value = null, bool $lazy = false): static|string|null
	{
		if ($value === null) {
			return $this->headers()[$key] ?? null;
		}

		if ($value === false) {
			unset($this->headers[$key]);
			return $this;
		}

		if ($lazy === true && isset($this->headers[$key]) === true) {
			return $this;
		}

		$this->headers[$key] = $value;
		return $this;
	}

	/**
	 * Setter and getter for all headers
	 *
	 * @return array|$this
	 */
	public function headers(array|null $headers = null): static|array
	{
		if ($headers === null) {
			$injectedHeaders = [];
			$isPrivate = static::isPrivate($this->usesAuth(), $this->usesCookies());

			if ($isPrivate === true) {
				// never ever cache private responses
				$injectedHeaders['Cache-Control'] = 'no-store, private';
			}

			// inject CORS headers if enabled
			$corsHeaders = Cors::headers();
			if ($corsHeaders !== []) {
				$injectedHeaders = [...$injectedHeaders, ...$corsHeaders];
			}

			if ($isPrivate === false) {
				// the response is public, but it may
				// vary based on request headers
				$vary = [];

				if ($this->usesAuth() === true) {
					$vary[] = 'Authorization';
				}

				if ($this->usesCookies() !== []) {
					$vary[] = 'Cookie';
				}

				// merge Vary from CORS if present
				if (isset($injectedHeaders['Vary']) === true) {
					// split CORS Vary into individual values to avoid duplication
					$corsVaryValues = array_map('trim', explode(',', $injectedHeaders['Vary']));
					$vary = [...$vary, ...$corsVaryValues];
				}

				if ($vary !== []) {
					$injectedHeaders['Vary'] = implode(', ', $vary);
				}
			}

			// lazily inject (never override custom headers)
			return [...$injectedHeaders, ...$this->headers];
		}

		$this->headers = $headers;
		$this->volatileHeaders([]);
		return $this;
	}

	/**
	 * Shortcut to configure a json response
	 *
	 * @return string|$this
	 */
	public function json(array|null $json = null): static|string
	{
		if ($json !== null) {
			$this->body(json_encode($json));
		}

		return $this->type('application/json');
	}

	/**
	 * Shortcut to create a redirect response
	 *
	 * @return $this
	 */
	public function redirect(
		string|null $location = null,
		int|null $code = null
	): static {
		$location = Url::to($location ?? '/');
		$location = Url::unIdn($location);

		return $this
			->header('Location', (string)$location)
			->code($code ?? 302);
	}

	/**
	 * Creates and returns the response object from the config
	 */
	public function send(HttpResponse|string|null $body = null): HttpResponse
	{
		if ($body instanceof HttpResponse) {
			// inject headers from the responder into the response
			// (only if they are not already set);
			$body->setHeaderFallbacks($this->headers());
			return $body;
		}

		if ($body !== null) {
			$this->body($body);
		}

		return new Response($this->toArray());
	}

	/**
	 * Converts the response configuration
	 * to an array
	 */
	public function toArray(): array
	{
		// the `cache`, `expires`, `usesAuth`, `usesCookies` and
		// `volatileHeaders` values are explicitly *not* serialized
		// as they are volatile and not to be exported
		return [
			'body'    => $this->body(),
			'code'    => $this->code(),
			'headers' => $this->headers(),
			'type'    => $this->type(),
		];
	}

	/**
	 * Converts the response configuration to an array
	 * that can safely be cached
	 */
	public function toCacheArray(): array
	{
		$response = $this->toArray();
		$volatile = $this->volatileHeaders()->collect();

		if ($volatile === []) {
			return $response;
		}

		$response['headers'] = $this->volatileHeaders()->strip($response['headers'], $volatile);
		return $response;
	}

	/**
	 * Setter and getter for the content type
	 *
	 * @return string|null|$this
	 */
	public function type(string|null $type = null): static|string|null
	{
		if ($type === null) {
			return $this->type;
		}

		if (Str::contains($type, '/') === false) {
			$type = Mime::fromExtension($type);
		}

		$this->type = $type;
		return $this;
	}

	/**
	 * Checks whether the response needs to be exempted from
	 * all caches due to using dynamic data based on auth
	 * and/or cookies; the request data only matters if it
	 * is actually used/relied on by the response
	 *
	 * @unstable
	 */
	public static function isPrivate(bool $usesAuth, array $usesCookies): bool
	{
		$infinri = App::instance();

		if ($usesAuth === true && $infinri->request()->hasAuth() === true) {
			return true;
		}

		foreach ($usesCookies as $cookie) {
			if (isset($_COOKIE[$cookie]) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Setter and getter for the volatile headers manager
	 */
	public function volatileHeaders(VolatileHeaders|array|null $headers = null): VolatileHeaders
	{
		if ($headers === null) {
			return $this->volatileHeaders ??= new VolatileHeaders();
		}

		if ($headers instanceof VolatileHeaders) {
			return $this->volatileHeaders = $headers;
		}

		$volatileHeaders = new VolatileHeaders();
		foreach ($headers as $name => $values) {
			$volatileHeaders->mark($name, $values);
		}

		return $this->volatileHeaders = $volatileHeaders;
	}
}
