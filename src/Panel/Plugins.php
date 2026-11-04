<?php

namespace Infinri\Panel;

use Infinri\Cms\App;
use Infinri\Filesystem\F;
use Infinri\Toolkit\A;
use Infinri\Toolkit\Str;

class Plugins
{
	/**
	 * Cache of all collected plugin files
	 */
	public array|null $files = null;

	/**
	 * Collects and returns the plugin files for all plugins
	 */
	public function files(): array
	{
		if ($this->files !== null) {
			return $this->files;
		}

		$this->files = [];

		foreach (App::instance()->plugins() as $plugin) {
			$this->files[] = $plugin->root() . '/index.css';
			$this->files[] = $plugin->root() . '/index.js';
		}

		return $this->files;
	}

	/**
	 * Returns the last modification
	 * of the collected plugin files
	 */
	public function modified(): int
	{
		$files    = $this->files();
		$modified = [0];

		foreach ($files as $file) {
			$modified[] = F::modified($file);
		}

		return max($modified);
	}

	/**
	 * Read the files from all plugins and concatenate them
	 */
	public function read(string $type): string
	{
		$dist = [];

		foreach ($this->files() as $file) {
			// filter out files with a different type
			if (F::extension($file) !== $type) {
				continue;
			}

			// filter out empty files and files that don't exist
			$content = F::read($file);
			if (!$content) {
				continue;
			}

			if ($type === 'js') {
				$content = trim($content);

				// make sure that each plugin is ended correctly
				if (Str::endsWith($content, ';') === false) {
					$content .= ';';
				}
			}

			$dist[] = $content;
		}

		return implode(PHP_EOL . PHP_EOL, $dist);
	}

	/**
	 * Absolute url to the cache file
	 * This is used by the panel to link the plugins
	 */
	public function url(string $type): string
	{
		return App::instance()->url('media') . '/plugins/index.' . $type . '?' . $this->modified();
	}
}
