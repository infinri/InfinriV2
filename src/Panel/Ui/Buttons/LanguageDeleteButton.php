<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Toolkit\I18n;

class LanguageDeleteButton extends ViewButton
{
	public function __construct(Language $language)
	{
		$user       = App::instance()->user();
		$permission = $user?->role()->permissions()->for('languages', 'delete');

		parent::__construct(
			dialog: 'languages/' . $language->id() . '/delete',
			disabled: $permission !== true,
			icon: 'trash',
			title: I18n::translate('delete'),
		);
	}
}
