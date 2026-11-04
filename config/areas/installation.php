<?php

use Infinri\Panel\Panel;
use Infinri\Toolkit\I18n;

return function ($infinri) {
	return [
		'icon'  => 'settings',
		'label' => I18n::translate('view.installation'),
		'views' => [
			'installation' => [
				'pattern' => 'installation',
				'auth'    => false,
				'action'  => function () use ($infinri) {
					$system = $infinri->system();
					return [
						'component' => 'k-installation-view',
						'props'     => [
							'isInstallable' => $system->isInstallable(),
							'isInstalled'   => $system->isInstalled(),
							'isOk'          => $system->isOk(),
							'requirements'  => $system->status(),
							'translations'  => $infinri->translations()->values(function ($translation) {
								return [
									'text'  => $translation->name(),
									'value' => $translation->code(),
								];
							}),
						]
					];
				}
			],
			'installation.fallback' => [
				'pattern' => '(:all)',
				'auth'    => false,
				'action'  => fn () => Panel::go('installation')
			]
		]
	];
};
