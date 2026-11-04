<?php

namespace Infinri\Cms;

class FilePermissions extends ModelPermissions
{
	protected const CATEGORY = 'files';

	/**
	 * Used to cache once determined permissions in memory
	 */
	protected static function cacheKey(ModelWithContent|Language $model): string
	{
		return $model->template() ?? '__none__';
	}

	protected function canChangeTemplate(): bool
	{
		if (count($this->model->blueprints()) <= 1) {
			return false;
		}

		return true;
	}
}
