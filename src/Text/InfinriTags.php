<?php

namespace Infinri\Text;

use Exception;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Toolkit\Str;

class InfinriTags
{
	public static function parse(
		string|null $text = null,
		array $data = [],
		bool $debug = false
	): string {
		// make sure $text is a string
		$text ??= '';

		$regex = '!
            (?=[^\]])               # positive lookahead that matches a group after the main expression without including ] in the result
            (?=\([a-z0-9_-]+:)      # positive lookahead that requires starts with ( and lowercase ASCII letters, digits, underscores or hyphens followed with : immediately to the right of the current location
            (\(                     # capturing group 1
                (?:[^()]+|(?1))*+   # repetitions of any chars other than ( and ) or the whole group 1 pattern (recursed)
            \))                     # end of capturing group 1
        !isx';

		return preg_replace_callback($regex, function ($match) use ($data, $debug) {
			try {
				return InfinriTag::parse($match[0], $data)->render();
			} catch (InvalidArgumentException $e) {
				if (Str::startsWith($e->getMessage(), 'Undefined tag type:') === true) {
					return $match[0];
				}

				// stay silent in production and ignore non-existing tags
				if ($debug !== true) {
					error_log($e);

					return $match[0];
				}

				throw $e;
			} catch (Exception $e) {
				if ($debug === true) {
					throw $e;
				}

				error_log($e);

				return $match[0];
			}
		}, $text);
	}
}
