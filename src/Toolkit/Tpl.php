<?php

namespace Infinri\Toolkit;

use Infinri\Filesystem\F;
use Throwable;

class Tpl
{
	/**
	 * Renders the template
	 *
	 * @throws Throwable
	 */
	public static function load(
		string|null $file = null,
		array $data = []
	): string {
		if ($file === null || is_file($file) === false) {
			return '';
		}

		ob_start();

		try {
			F::load($file, null, $data);
		} catch (Throwable $e) {
			$exception = $e;
		}

		$content = ob_get_contents();
		ob_end_clean();

		if (isset($exception) === true) {
			throw $exception;
		}

		return $content;
	}
}
