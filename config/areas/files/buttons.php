<?php

use Infinri\Cms\File;
use Infinri\Panel\Ui\Buttons\OpenButton;
use Infinri\Panel\Ui\Buttons\SettingsButton;

return [
	'file.open' => function (File $file) {
		return new OpenButton(link: $file->previewUrl());
	},
	'file.settings' => function (File $file) {
		return new SettingsButton(model: $file);
	}
];
