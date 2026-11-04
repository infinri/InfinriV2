<?php

use Infinri\Cms\System;
use Infinri\Exception\PermissionException;
use Infinri\Toolkit\Str;

/**
 * System
 */
return [
	'fields' => [
		'ascii'           => fn () => Str::$ascii,
		'authStatus'      => fn () => $this->infinri()->auth()->status()->toArray(),
		'defaultLanguage' => fn () => $this->infinri()->panelLanguage(),
		'isOk'            => fn (System $system) => $system->isOk(),
		'isInstallable'   => fn (System $system) => $system->isInstallable(),
		'isInstalled'     => fn (System $system) => $system->isInstalled(),
		'isLocal'         => fn (System $system) => $system->isLocal(),
		'multilang'       => fn () => $this->infinri()->option('languages', false) !== false,
		'languages'       => fn () => $this->infinri()->languages(),
		'locales'         => function () {
			$locales = [];
			$translations = $this->infinri()->translations();
			foreach ($translations as $translation) {
				$locales[$translation->code()] = $translation->locale();
			}
			return $locales;
		},
		'loginMethods' => fn (System $system) => array_keys($system->loginMethods()),
		'requirements' => fn (System $system) => $system->toArray(),
		'site'         => fn (System $system) => $system->title(),
		'slugs'        => fn () => Str::$language,
		// provide the value even if `site.access` permission is disabled
		'title'       => fn () => $this->infinri()->site()->title()->value(),
		'translation' => function () {
			$infinri = $this->infinri();
			$code = $infinri->user()?->language() ??
					$infinri->panelLanguage();

			return
				$infinri->translation($code) ??
				$infinri->translation('en');
		},
		'infinritext' => fn () => $this->infinri()->option('panel.infinritext') ?? true,
		'user'      => fn () => $this->user(),
		'version'   => function () {
			try {
				$this->validateAreaAccess('system');
				return $this->infinri()->version();
			} catch (PermissionException) {
				return null;
			}
		}
	],
	'type'   => System::class,
	'views'  => [
		'login' => [
			'authStatus',
			'isOk',
			'isInstallable',
			'isInstalled',
			'loginMethods',
			'title',
			'translation'
		],
		'troubleshooting' => [
			'isOk',
			'isInstallable',
			'isInstalled',
			'title',
			'translation',
			'requirements'
		],
		'panel' => [
			'ascii',
			'defaultLanguage',
			'isOk',
			'isInstalled',
			'isLocal',
			'infinritext',
			'languages',
			'locales',
			'multilang',
			'requirements',
			'site',
			'slugs',
			'title',
			'translation',
			'user' => 'auth',
			'version'
		]
	],
];
