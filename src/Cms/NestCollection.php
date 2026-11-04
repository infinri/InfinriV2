<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Toolkit\Collection as BaseCollection;

class NestCollection extends BaseCollection
{
	/**
	 * Converts all objects in the collection
	 * to an array. This can also take a callback
	 * function to further modify the array result.
	 */
	public function toArray(Closure|null $map = null): array
	{
		return parent::toArray(
			$map ?? fn ($object) => $object->toArray()
		);
	}
}
