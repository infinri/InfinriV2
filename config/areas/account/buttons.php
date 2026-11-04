<?php

use Infinri\Cms\App;
use Infinri\Cms\User;
use Infinri\Panel\Ui\Buttons\ViewButton;

return [
	'user.theme' => function (App $infinri, User $user) {
		if ($infinri->user()->is($user) === true) {
			return new ViewButton(component: 'k-theme-view-button');
		}
	}
];
