<?php

use Infinri\Toolkit\I18n;

return function ($infinri) {
	$blueprint = $infinri->site()->blueprint();

	return [
		'breadcrumbLabel' => function () use ($infinri) {
			return $infinri->site()->title()->or(I18n::translate('view.site'))->toString();
		},
		'icon'      => $blueprint->icon() ?? 'home',
		'label'     => $blueprint->title() ?? I18n::translate('view.site'),
		'menu'      => true,
		'buttons'   => require __DIR__ . '/site/buttons.php',
		'dialogs'   => require __DIR__ . '/site/dialogs.php',
		'drawers'   => require __DIR__ . '/site/drawers.php',
		'dropdowns' => require __DIR__ . '/site/dropdowns.php',
		'requests'  => require __DIR__ . '/site/requests.php',
		'searches'  => require __DIR__ . '/site/searches.php',
		'views'     => require __DIR__ . '/site/views.php',
	];
};
