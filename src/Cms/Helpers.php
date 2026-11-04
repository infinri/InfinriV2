<?php

namespace Infinri\Cms;

use Closure;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Toolkit\Str;

class Helpers
{
	/**
	 * Simple object and variable dumper
	 * to help with debugging.
	 */
	public static function dump(mixed $variable, bool $echo = true): string
	{
		$infinri  = App::instance();
		$output = print_r($variable, true);

		if ($infinri->environment()->cli() === true) {
			$output .= PHP_EOL;
		} else {
			$output = Str::wrap($output, '<pre>', '</pre>');
		}

		if ($echo === true) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Performs an action with custom handling
	 * for all PHP errors and warnings
	 *
	 * @param \Closure $action Any action that may cause an error or warning
	 * @param \Closure $condition Closure that returns bool to determine if to
	 *                            suppress an error, receives arguments for
	 *                            `set_error_handler()`
	 * @param mixed $fallback Value to return when error is suppressed
	 * @return mixed Return value of the `$action` closure,
	 *               possibly overridden by `$fallback`
	 */
	public static function handleErrors(
		Closure $action,
		Closure $condition,
		$fallback = null
	) {
		$override = null;

		// check if the LC_MESSAGES constant is defined
		// some environments do not support LC_MESSAGES especially on Windows
		// LC_MESSAGES constant is available if PHP was compiled with libintl
		if (defined('LC_MESSAGES') === true) {
			// backup current locale
			$locale = setlocale(LC_MESSAGES, 0);

			// set locale to C so that errors and warning messages are
			// printed in English for robust comparisons in the handler
			setlocale(LC_MESSAGES, 'C');
		}

		/**
		 * @psalm-suppress UndefinedVariable
		 */
		$handler = set_error_handler(function () use (&$override, &$handler, $condition, $fallback) {
			// check if suppress condition is met
			$suppress = $condition(...func_get_args());

			if ($suppress !== true) {
				// handle other warnings with Whoops if loaded
				if (is_callable($handler) === true) {
					return $handler(...func_get_args());
				}

				// otherwise use the standard error handler
				return false; // @codeCoverageIgnore
			}

			// use fallback to override return for suppressed errors
			$override = $fallback;

			if (is_callable($override) === true) {
				$override = $override();
			}

			// no additional error handling
			return true;
		});

		try {
			$result = $action();
		} finally {
			// always restore the error handler, even if the
			// action or the standard error handler threw an
			// exception; this avoids modifying global state
			restore_error_handler();

			// check if the LC_MESSAGES constant is defined
			if (defined('LC_MESSAGES') === true) {
				// reset to original locale
				setlocale(LC_MESSAGES, $locale);
			}
		}

		return $override ?? $result;
	}

	/**
	 * Checks if a helper was overridden by the user
	 * by setting the `INFINRI_HELPER_*` constant
	 *
	 * @param string $name Name of the helper
	 */
	public static function hasOverride(string $name): bool
	{
		$name = 'INFINRI_HELPER_' . strtoupper($name);
		return defined($name) === true && constant($name) === false;
	}

	/**
	 * Determines the size/length of numbers,
	 * strings, arrays and countable objects
	 *
	 * @throws \Infinri\Exception\InvalidArgumentException
	 */
	public static function size(mixed $value): int
	{
		if (is_numeric($value)) {
			return (int)$value;
		}

		if (is_string($value)) {
			return Str::length(trim($value));
		}

		if (is_countable($value)) {
			return count($value);
		}

		throw new InvalidArgumentException(
			message: 'Could not determine the size of the given value'
		);
	}
}
