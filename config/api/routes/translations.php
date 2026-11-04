<?php

/**
 * Translations Routes
 */
return [
	[
		'pattern' => 'translations',
		'method'  => 'GET',
		'auth'    => false,
		'action'  => function () {
			return $this->infinri()->translations();
		}
	],
	[
		'pattern' => 'translations/(:any)',
		'method'  => 'GET',
		'auth'    => false,
		'action'  => function (string $code) {
			return $this->infinri()->translations()->find($code);
		}
	]

];
