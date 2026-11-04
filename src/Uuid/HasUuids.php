<?php

namespace Infinri\Uuid;

trait HasUuids
{
	/**
	 * Find a single element by global UUID
	 */
	protected function findByUuid(
		string $uuid,
		string|array|null $scheme = null
	): Identifiable|null {
		// handle UUID shortcuts with a leading @
		if ($scheme !== null && str_starts_with($uuid, '@') === true) {
			$uuid = $scheme . '://' . substr($uuid, 1);
		}

		if (Uuid::is($uuid, $scheme) === true) {
			// look up model by UUID while prioritizing
			// $this collection when searching
			return Uuid::for($uuid, $this)->model();
		}

		return null;
	}
}
