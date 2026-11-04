<?php

namespace Infinri\Content;

use Infinri\Cms\Language;
use WeakMap;

class VersionCache
{
	/**
	 * All cache values for all versions
	 * and language combinations
	 */
	protected static WeakMap $cache;

	/**
	 * Tries to receive a fields for a version/language combination
	 */
	public static function get(Version $version, Language $language): array|null
	{
		$model = $version->model();
		$key   = $version->id() . ':' . $language->code();

		return static::$cache[$model][$key] ?? null;
	}

	/**
	 * Removes fields for a version/language combination
	 */
	public static function remove(Version $version, Language $language): void
	{
		$model = $version->model();

		if (isset(static::$cache[$model]) === false) {
			return;
		}

		// Avoid indirect manipulation of WeakMap
		$key = $version->id() . ':' . $language->code();
		$map = static::$cache[$model];
		unset($map[$key]);
		static::$cache[$model] = $map;
	}

	/**
	 * Resets the cache
	 */
	public static function reset(): void
	{
		static::$cache = new WeakMap();
	}

	/**
	 * Keeps fields for a version/language combination
	 */
	public static function set(
		Version $version,
		Language $language,
		array $fields = []
	): void {
		$model = $version->model();
		$key   = $version->id() . ':' . $language->code();

		static::$cache ??= new WeakMap();
		static::$cache[$model] ??= [];
		static::$cache[$model][$key] = $fields;
	}
}
