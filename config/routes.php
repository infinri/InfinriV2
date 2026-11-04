<?php

use Infinri\Cms\App;
use Infinri\Cms\Cors;
use Infinri\Cms\LanguageRoutes;
use Infinri\Cms\Media;
use Infinri\Http\Response;
use Infinri\Panel\Panel;
use Infinri\Panel\Plugins;
use Infinri\Plugin\Assets;
use Infinri\Toolkit\Str;
use Infinri\Uuid\Uuid;

return function (App $infinri) {
	$api   = $infinri->option('api.slug', 'api');
	$panel = $infinri->option('panel.slug', 'panel');
	$index = $infinri->url('index');
	$media = $infinri->url('media');

	if (Str::startsWith($media, $index) === true) {
		$media = Str::after($media, $index);
	} else {
		// media URL is outside of the site, we can't make routing work;
		// fall back to the standard media route
		$media = 'media';
	}

	/**
	 * Before routes are running before the
	 * plugin routes and cannot be overwritten by
	 * plugins.
	 */
	$before = [
		// handle CORS preflight requests
		[
			'pattern' => '(:all)',
			'method'  => 'OPTIONS',
			'action'  => function () use ($infinri) {
				$request = $infinri->request();

				// skip if no CORS request
				if ($request->header('Access-Control-Request-Method') === null) {
					/** @var \Infinri\Http\Route $this */
					$this->next();
				}

				// skip if CORS is disabled
				if ($infinri->isCorsEnabled() === false) {
					/** @var \Infinri\Http\Route $this */
					$this->next();
				}

				$headers = Cors::headers(preflight: true);

				// block if origin doesn't match
				if ($headers === []) {
					return null;
				}

				return new Response('', null, 204, $headers);
			}
		],
		[
			'pattern' => $api . '/(:all)',
			'method'  => 'ALL',
			'env'     => 'api',
			'action'  => function (string|null $path = null) use ($infinri) {
				if ($infinri->option('api') === false) {
					return null;
				}

				$request = $infinri->request();

				return $infinri->api()->render($path, $this->method(), [
					'body'    => $request->body()->toArray(),
					'files'   => $request->files()->toArray(),
					'headers' => $request->headers(),
					'query'   => $request->query()->toArray(),
				]);
			}
		],
		[
			'pattern' => $media . '/plugins/index.(css|js)',
			'env'     => 'media',
			'action'  => function (string $type) use ($infinri) {
				$plugins = new Plugins();

				return $infinri
					->response()
					->type($type)
					->body($plugins->read($type));
			}
		],
		[
			// TODO: change to '/plugins/(:any)/(:any)/(:any)/(:all)' once
			// the hash is made mandatory
			'pattern' => $media . '/plugins/(:any)/(:any)/(?:(:any)/)?(:all)',
			'env'     => 'media',
			'action'  => function (
				string $provider,
				string $pluginName,
				string $hash,
				string $path
			) {
				return Assets::resolve(
					$provider . '/' . $pluginName,
					$hash,
					$path
				);
			}
		],
		[
			'pattern' => $media . '/pages/(:all)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $path,
				string $hash,
				string $filename
			) use ($infinri) {
				return Media::link($infinri->page($path), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/site/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $hash,
				string $filename
			) use ($infinri) {
				return Media::link($infinri->site(), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/users/(:any)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $id,
				string $hash,
				string $filename
			) use ($infinri) {
				return Media::link($infinri->user($id), $hash, $filename);
			}
		],
		[
			'pattern' => $media . '/assets/(:all)/(:any)/(:any)',
			'env'     => 'media',
			'action'  => function (
				string $path,
				string $hash,
				string $filename
			) {
				return Media::thumb($path, $hash, $filename);
			}
		],
		[
			'pattern' => $panel . '/(:all?)',
			'method'  => 'ALL',
			'env'     => 'panel',
			'action'  => function (string|null $path = null) {
				return Panel::router($path);
			}
		],
		// permalinks for page/file UUIDs
		[
			'pattern' => '@/(page|file)/(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $type, string $id) use ($infinri) {
				// try to resolve to model, but only from UUID cache;
				// this ensures that only existing UUIDs can be queried
				// and attackers can't force Infinri to go through the whole
				// site index with a non-existing UUID
				if ($model = Uuid::for($type . '://' . $id)?->model(true)) {
					return $infinri
						->response()
						->redirect($model->url());
				}

				// render the error page
				return false;
			}
		],
	];

	// Multi-language setup
	if ($infinri->multilang() === true) {
		$after = LanguageRoutes::create($infinri);
	} else {
		// Single-language home
		$after[] = [
			'pattern' => '',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function () use ($infinri) {
				return $infinri->resolve();
			}
		];

		// redirect the home page folder to the real homepage
		$after[] = [
			'pattern' => $infinri->option('home', 'home'),
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function () use ($infinri) {
				return $infinri
					->response()
					->redirect($infinri->site()->url());
			}
		];

		// Single-language subpages
		$after[] = [
			'pattern' => '(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $path) use ($infinri) {
				return $infinri->resolve($path);
			}
		];
	}

	return [
		'before' => $before,
		'after'  => $after
	];
};
