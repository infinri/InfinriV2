<?php

namespace Kirby\Cms;

class Response extends \Kirby\Http\Response
{
	/**
	 * Adjusted redirect creation which
	 * parses locations with the Url::to method
	 * first.
	 */
	public static function redirect(
		string $location = '/',
		int $code = 302
	): static {
		return parent::redirect(Url::to($location), $code);
	}
}
