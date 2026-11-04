<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\Cms\App;
use Infinri\Cms\Language;
use Infinri\Toolkit\I18n;

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
