<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\Cms\ModelWithContent;
use Infinri\Toolkit\I18n;

class SettingsButton extends ViewButton
{
	public function __construct(
		ModelWithContent $model
	) {
		parent::__construct(
			component: 'k-settings-view-button',
			class: 'k-settings-view-button',
			icon: 'cog',
			options: $model->panel()->url(true),
			title: I18n::translate('settings'),
		);
	}
}
