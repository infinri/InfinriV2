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
		$infinri  = App::instance();
		$assets = new Assets();

		// Full HTML response
		// @codeCoverageIgnoreStart
		try {
			if ($assets->link() === true) {
				usleep(1);
				Response::go($infinri->url('base') . '/' . $infinri->path());
			}
		} catch (Throwable $e) {
			die('The Panel assets cannot be installed properly. ' . $e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		// get the uri object for the panel url
		$uri = new Uri($infinri->url('panel'));

		// proper response code
		$code = $fiber['$view']['code'] ?? 200;

		// load the main Panel view template
		$body = Tpl::load($infinri->root('infinri') . '/views/panel.php', [
			'assets'   => $assets->external(),
			'icons'    => $assets->icons(),
			'nonce'    => $infinri->nonce(),
			'fiber'    => $fiber,
			'panelUrl' => $uri->path()->toString(true) . '/',
		]);

		$frameAncestors = $infinri->option('panel.frameAncestors');
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
