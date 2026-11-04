<?php

use Infinri\Cms\App;

return [
	'search' => [
		'pattern' => 'search',
		'action'  => function () {
			return [
				'component' => 'k-search-view',
				'props' => [
					'type' => App::instance()->request()->get('type'),
				]
			];
		}
	],
];
