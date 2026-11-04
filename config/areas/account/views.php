<?php

use Infinri\Cms\App;
use Infinri\Cms\Find;
use Infinri\Toolkit\I18n;

return [
	'account' => [
		'pattern' => 'account',
		'action'  => fn () => [
			'component' => 'k-account-view',
			'props'     => Find::user()->panel()->props(),
		],
	],
	'account.file' => [
		'pattern' => 'account/files/(:any)',
		'action'  => function (string $filename) {
			return Find::file('account', $filename)->panel()->view();
		}
	],
	'account.password' => [
		'pattern' => 'reset-password',
		'action'  => fn () => [
			'component' => 'k-reset-password-view',
			'breadcrumb' => [
				[
					'label' => I18n::translate('view.resetPassword')
				]
			],
			'props' => [
				'requirePassword' => App::instance()->auth()->isResettingPassword() === false
			]
		]
	]
];
