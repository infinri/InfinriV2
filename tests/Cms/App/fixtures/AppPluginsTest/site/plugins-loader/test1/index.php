<?php

use Infinri\Cms\AppPluginsTest;

Infinri::plugin('infinri/test1', [
	'hooks' => [
		'system.loadPlugins:after' => function () {
			AppPluginsTest::$calledPluginsLoadedHook = true;
		}
	]
]);
