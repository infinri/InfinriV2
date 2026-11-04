<?php

namespace Infinri\Cms;

use Infinri\Content\Field;
use Infinri\Toolkit\Obj;

class NestObject extends Obj
{
	/**
	 * Converts the object to an array
	 */
	public function toArray(): array
	{
		$result = [];

		foreach ((array)$this as $key => $value) {
			if ($value instanceof Field) {
				$result[$key] = $value->value();
				continue;
			}

			if (
				is_object($value) === true &&
				method_exists($value, 'toArray')
			) {
				$result[$key] = $value->toArray();
				continue;
			}

			$result[$key] = $value;
		}

		return $result;
	}
}
