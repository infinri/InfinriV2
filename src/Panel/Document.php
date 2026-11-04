<?php

namespace Infinri\Panel;

use Infinri\Cms\App;
use Infinri\Http\Response;
use Infinri\Http\Uri;
use Infinri\Toolkit\Tpl;
use Throwable;

class Document
{
	/**
	 * Renders the panel document
	 */
	public static function response(array $fiber): Response
	{
		$kirby  = App::instance();
		$assets = new Assets();

		// Full HTML response
		// @codeCoverageIgnoreStart
		try {
			if ($assets->link() === true) {
				usleep(1);
				Response::go($kirby->url('base') . '/' . $kirby->path());
			}
		} catch (Throwable $e) {
			die('The Panel assets cannot be installed properly. ' . $e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		// get the uri object for the panel url
		$uri = new Uri($kirby->url('panel'));

		// proper response code
		$code = $fiber['$view']['code'] ?? 200;

		// load the main Panel view template
		$body = Tpl::load($kirby->root('kirby') . '/views/panel.php', [
			'assets'   => $assets->external(),
			'icons'    => $assets->icons(),
			'nonce'    => $kirby->nonce(),
			'fiber'    => $fiber,
			'panelUrl' => $uri->path()->toString(true) . '/',
		]);

		$frameAncestors = $kirby->option('panel.frameAncestors');
		$frameAncestors = match (true) {
			$frameAncestors === true   => "'self'",
			is_array($frameAncestors)  => "'self' " . implode(' ', $frameAncestors),
			is_string($frameAncestors) => $frameAncestors,
			default                    => "'none'"
		};

		return new Response($body, 'text/html', $code, [
			'Content-Security-Policy' => 'frame-ancestors ' . $frameAncestors,
			'X-Robots-Tag'            => 'noindex, nofollow'
		]);
	}
}
