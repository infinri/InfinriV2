<?php

namespace Infinri\Cms;

class LanguagePermissions extends ModelPermissions
{
	protected const CATEGORY = 'languages';

	protected function canDelete(): bool
	{
		return $this->model->isDeletable() === true;
	}
}
