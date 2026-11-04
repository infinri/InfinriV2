<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\Cms\App;
use Infinri\Cms\Language;
use Infinri\Toolkit\I18n;

class LanguageSettingsButton extends ViewButton
{
	public function __construct(Language $language)
	{
		$user       = App::instance()->user();
		$permission = $user?->role()->permissions()->for('languages', 'update');

		parent::__construct(
			dialog: 'languages/' . $language->id() . '/update',
			disabled: $permission !== true,
			icon: 'cog',
			title: I18n::translate('settings'),
		);
	}
}
