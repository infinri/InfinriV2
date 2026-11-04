<?php

use Infinri\Cms\App;
use Infinri\Http\Cookie;
use Infinri\Panel\Ui\Buttons\ViewButtons;
use Infinri\Toolkit\I18n;

return [
	'system' => [
		'pattern' => 'system',
		'action'  => function () {
			$infinri        = App::instance();
			$system       = $infinri->system();
			$updateStatus = $system->updateStatus();
			$debugMode    = $infinri->option('debug', false) === true;
			$isLocal      = $system->isLocal();

			$environment = [
				[
					'label' => $updateStatus?->label() ?? I18n::translate('version'),
					'value' => $infinri->version(),
					'link'  => $updateStatus?->url(),
					'theme' => $updateStatus?->theme(),
					'icon'  => $updateStatus?->icon() ?? 'info'
				],
				[
					'label' => 'PHP',
					'value' => phpversion(),
					'icon'  => 'code'
				],
				[
					'label' => I18n::translate('server'),
					'value' => $system->serverSoftwareShort() ?? '?',
					'icon'  => 'server'
				]
			];

			$exceptions = $updateStatus?->exceptionMessages() ?? [];

			$plugins = $system->plugins()->values(function ($plugin) use (&$exceptions) {
				$authors      = $plugin->authorsNames();
				$updateStatus = $plugin->updateStatus();
				$version      = $updateStatus?->toArray();
				$version    ??= $plugin->version() ?? '–';

				if ($updateStatus !== null) {
					$exceptions = [
						...$exceptions,
						...$updateStatus->exceptionMessages()
					];
				}

				return [
					'author'  => empty($authors) ? '–' : $authors,
					'name'    => [
						'text' => $plugin->name() ?? '–',
						'href' => $plugin->link(),
					],
					'version' => $version,
				];
			});

			$security = $updateStatus?->messages() ?? [];

			if ($isLocal === true) {
				$security[] = [
					'id'    => 'local',
					'icon'  => 'info',
					'theme' => 'info',
					'text'  => I18n::translate('system.issues.local')
				];
			}

			if ($debugMode === true) {
				$security[] = [
					'id'    => 'debug',
					'icon'  => $isLocal ? 'info' : 'alert',
					'theme' => $isLocal ? 'info' : 'negative',
					'text'  => I18n::translate('system.issues.debug'),
					'link'  => 'https://infinri.com/security/debug'
				];
			}

			if (
				$isLocal === false &&
				$infinri->environment()->https() !== true
			) {
				$security[] = [
					'id'   => 'https',
					'text' => I18n::translate('system.issues.https'),
					'link' => 'https://infinri.com/security/https'
				];
			}

			if ($infinri->option('panel.vue.compiler', null) === null) {
				$security[] = [
					'id'    => 'vue-compiler',
					'link'  => 'https://infinri.com/security/vue-compiler',
					'text'  => I18n::translate('system.issues.vue.compiler'),
					'theme' => 'notice'
				];
			}

			if ($infinri->option('content.salt') === null) {
				$security[] = [
					'id'    => 'content-salt',
					'link'  => 'https://infinri.com/security/content-salt',
					'text'  => I18n::translate('system.issues.content.salt'),
					'theme' => 'notice'
				];
			}

			if (($infinri->option('cookie.key') ?? Cookie::$key) === 'InfinriHttpCookieKey') {
				$security[] = [
					'id'    => 'cookie-key',
					'link'  => 'https://infinri.com/security/cookie-key',
					'text'  => I18n::translate('system.issues.cookie.key'),
					'theme' => 'notice'
				];
			}

			// sensitive URLs
			if ($isLocal === false) {
				$sensitive = [
					'content' => $system->exposedFileUrl('content'),
					'git'     => $system->exposedFileUrl('git'),
					'infinri'   => $system->exposedFileUrl('infinri'),
					'site'    => $system->exposedFileUrl('site')
				];
			}

			return [
				'component' => 'k-system-view',
				'props'     => [
					'buttons'     => fn () =>
						ViewButtons::view('system')->render(),
					'environment' => $environment,
					'exceptions'  => $debugMode ? $exceptions : [],
					'info'        => $system->info(),
					'plugins'     => $plugins,
					'security'    => $security,
					'urls'        => $sensitive ?? []
				]
			];
		}
	],
];
