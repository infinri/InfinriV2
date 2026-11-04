<?php

use Infinri\Cms\User;
use Infinri\Panel\Ui\Buttons\SettingsButton;
use Infinri\Panel\Ui\Buttons\ViewButton;
use Infinri\Toolkit\I18n;

return [
	'users.create' => function (User $user, string|null $role = null) {
		return new ViewButton(
			dialog: 'users/create?role=' . $role,
			disabled: $user->infinri()->roles()->canBeCreated()->count() < 1,
			icon: 'add',
			text: I18n::translate('user.create'),
		);
	},
	'user.settings' => function (User $user) {
		return new SettingsButton(model: $user);
	}
];
