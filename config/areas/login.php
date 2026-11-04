<?php

use Infinri\Panel\Panel;
use Infinri\Toolkit\I18n;

return function ($infinri) {
	return [
		'icon'  => 'user',
		'label' => I18n::translate('login'),
		'views' => [
			'login' => [
				'pattern' => 'login',
				'auth'    => false,
				'action'  => function () use ($infinri) {
					$system = $infinri->system();
					$status = $infinri->auth()->status();
					return [
						'component' => 'k-login-view',
						'props'     => [
							'methods' => array_keys($system->loginMethods()),
							'pending' => [
								'email'     => $status->email(),
								'challenge' => $status->challenge()
							]
						],
					];
				}
			],
			'login.fallback' => [
				'pattern' => '(:all)',
				'auth'    => false,
				'action'  => function ($path) use ($infinri) {
					/**
					 * Store the current path in the session
					 * Once the user is logged in, the path will
					 * be used to redirect to that view again
					 */
					$infinri->session()->set('panel.path', $path);
					Panel::go(url: 'login', refresh: 0);
				}
			]
		]
	];
};
