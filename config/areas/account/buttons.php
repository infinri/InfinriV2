<?php

use Infinri\Cms\App;
use Infinri\Cms\User;
use Infinri\Panel\Ui\Buttons\ViewButton;

return [
	'user.theme' => function (App $kirby, User $user) {
		if ($kirby->user()->is($user) === true) {
			return new ViewButton(component: 'k-theme-view-button');
		}
	}
];
