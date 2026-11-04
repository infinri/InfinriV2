<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

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
