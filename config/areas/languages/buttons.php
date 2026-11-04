<?php

use Infinri\Cms\Language;
use Infinri\Panel\Ui\Buttons\LanguageCreateButton;
use Infinri\Panel\Ui\Buttons\LanguageDeleteButton;
use Infinri\Panel\Ui\Buttons\LanguageSettingsButton;
use Infinri\Panel\Ui\Buttons\OpenButton;

return [
	'languages.create' => fn () =>
		new LanguageCreateButton(),
	'language.open' => fn (Language $language) =>
		new OpenButton(link: $language->url()),
	'language.settings' => fn (Language $language) =>
		new LanguageSettingsButton($language),
	'language.delete' => function (Language $language) {
		if ($language->isDeletable() === true) {
			return new LanguageDeleteButton($language);
		}
	}
];
