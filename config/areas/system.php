<?php

use Infinri\Toolkit\I18n;

return function ($infinri) {
	return [
		'icon'  => 'settings',
		'label' => I18n::translate('view.system'),
		'menu'  => true,
		'views' => require __DIR__ . '/system/views.php'
	];
};
