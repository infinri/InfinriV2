<?php

use Infinri\Toolkit\I18n;
use Infinri\Toolkit\Str;

return [
	'props' => [
		/**
		 * The label for the section. This can be a simple string or
		 * a template with additional info from the parent page.
		 */
		'label' => function ($label = null) {
			return I18n::translate($label, $label);
		}
	],
	'computed' => [
		'headline' => function () {
			if ($this->label) {
				return $this->model()->toString($this->label);
			}

			return Str::label($this->name);
		}
	]
];
