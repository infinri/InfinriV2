<?php

namespace Kirby\Toolkit;

abstract class Facade
{
	/**
	 * Returns the instance that should be
	 * available statically
	 */
	abstract public static function instance();

	/**
	 * Proxy for all public instance calls
	 */
	public static function __callStatic(string $method, array|null $args = null)
	{
		return static::instance()->$method(...$args);
	}
}
