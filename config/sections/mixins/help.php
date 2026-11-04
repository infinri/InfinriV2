<?php

use Infinri\Toolkit\I18n;

return [
	'props' => [
		/**
		 * Sets the help text
		 */
		'help' => function ($help = null) {
			return I18n::translate($help, $help);
		}
	],
	'computed' => [
		'help' => function () {
			if ($this->help) {
				$help = $this->model()->toSafeString($this->help);
				$help = $this->infinri()->infinritext($help);
				return $help;
			}
		}
	]
];
