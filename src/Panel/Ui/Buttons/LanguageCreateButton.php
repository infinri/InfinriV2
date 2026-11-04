<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\Cms\App;
use Infinri\Toolkit\I18n;

class LanguageCreateButton extends ViewButton
{
	public function __construct()
	{
		$user       = App::instance()->user();
		$permission = $user?->role()->permissions()->for('languages', 'create');

		parent::__construct(
			dialog: 'languages/create',
			disabled: $permission !== true,
			icon: 'add',
			text: I18n::translate('language.create'),
		);
	}
}
