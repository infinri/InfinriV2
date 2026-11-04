<?php

/**
 * Removes every docblock containing @package, @author, @link, @copyright or @license.
 *
 * Usage: php strip-docblocks.php [path ...]   (defaults to src/)
 */

$pattern = '/@(package|author|link|copyright|license)\b/';
$paths   = array_slice($argv, 1) ?: [__DIR__ . '/src'];
$changed = 0;

foreach ($paths as $path) {
	$files = is_dir($path)
		? new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)), '/\.php$/')
		: [new SplFileInfo($path)];

	foreach ($files as $file) {
		$file = $file->getPathname();
		if (preg_match('#/(vendor|dependencies)/#', $file)) {
			continue;
		}

		$code   = file_get_contents($file);
		$ranges = [];
		$offset = 0;

		foreach (token_get_all($code) as $token) {
			$text = is_array($token) ? $token[1] : $token;
			if (is_array($token) && $token[0] === T_DOC_COMMENT && preg_match($pattern, $text)) {
				$ranges[] = [$offset, $offset + strlen($text)];
			}
			$offset += strlen($text);
		}

		if ($ranges === []) {
			continue;
		}

		foreach (array_reverse($ranges) as [$start, $end]) {
			// if the block sits alone on its line, also remove its indentation and line break
			$s = $start;
			while ($s > 0 && ($code[$s - 1] === ' ' || $code[$s - 1] === "\t")) {
				$s--;
			}
			$e = $end;
			while ($e < strlen($code) && ($code[$e] === ' ' || $code[$e] === "\t")) {
				$e++;
			}
			if ($s === 0 || $code[$s - 1] === "\n") {
				if (substr($code, $e, 2) === "\r\n") {
					[$start, $end] = [$s, $e + 2];
				} elseif (substr($code, $e, 1) === "\n" || $e === strlen($code)) {
					[$start, $end] = [$s, $e + 1];
				}
			}
			$code = substr($code, 0, $start) . substr($code, $end);
		}

		file_put_contents($file, $code);
		echo "$file\n";
		$changed++;
	}
}

echo "$changed file(s) updated\n";
