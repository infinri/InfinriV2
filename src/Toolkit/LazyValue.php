<?php

namespace Infinri\Toolkit;

use Closure;

class LazyValue
{
	public function __construct(
		protected Closure $value
	) {
	}

	/**
	 * Resolve the lazy value to its actual value
	 */
	public function resolve(mixed ...$args): mixed
	{
		return call_user_func_array($this->value, $args);
	}

	/**
	 * Unwrap a single value or an array of values
	 */
	public static function unwrap(mixed $data, mixed ...$args): mixed
	{
		if (is_array($data) === true) {
			return A::map($data, fn ($value) => static::unwrap($value, ...$args));
		}

		if ($data instanceof static) {
			return $data->resolve(...$args);
		}

		return $data;
	}
}
