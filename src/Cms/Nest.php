<?php

namespace Kirby\Cms;

use Kirby\Content\Field;

class Nest
{
	public static function create(
		$data,
		object|null $parent = null
	): NestCollection|NestObject|Field {
		if (is_scalar($data) === true) {
			return new Field($parent, $data, $data);
		}

		$result = [];

		foreach ($data as $key => $value) {
			if (is_array($value) === true) {
				$result[$key] = static::create($value, $parent);
			} elseif (is_scalar($value) === true) {
				$result[$key] = new Field($parent, $key, $value);
			}
		}

		$key = key($data);

		if ($key === null || is_int($key) === true) {
			return new NestCollection($result);
		}

		return new NestObject($result);
	}
}
