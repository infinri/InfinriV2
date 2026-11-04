<?php

namespace Infinri\Panel;

use Infinri\Cms\App;
use Infinri\Cms\Url;
use Infinri\Exception\Exception;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Filesystem\Asset;
use Infinri\Filesystem\Dir;
use Infinri\Filesystem\F;
use Infinri\Toolkit\A;
class Assets
{
	protected bool $isDev;
	protected App $infinri;
	protected string $nonce;
	protected Plugins $plugins;
	protected string $url;
	protected bool $vite;

	public function __construct()
	{
		$this->infinri   = App::instance();
		$this->nonce   = $this->infinri->nonce();
		$this->plugins = new Plugins();

		$vite       = $this->infinri->roots()->panel() . '/.vite-running';
		$this->vite = is_file($vite) === true;

		// Check if Panel is running in dev mode to
		// get the assets from the Vite dev server;
		// dev mode = explicitly enabled in the config AND Vite is running
		$this->isDev =
			$this->infinri->option('panel.dev', false) !== false &&
			$this->vite === true;

		// Get the base URL
		$this->url = $this->url();
	}

	/**
	 * Get all CSS files
	 */
	public function css(): array
	{
		$css = [
			'index'   => $this->url . '/css/style.min.css',
			'plugins' => $this->plugins->url('css'),
			...$this->custom('panel.css')
		];

		// during dev mode we do not need to load
		// the general stylesheet (as styling will be inlined)
		if ($this->isDev === true) {
			$css['index'] = null;
		}

		return array_filter($css);
	}

	/**
	 * Check for a custom asset file from the
	 * config (e.g. panel.css or panel.js)
	 */
	public function custom(string $option): array
	{
		$customs = [];

		if ($assets = $this->infinri->option($option)) {
			$assets  = A::wrap($assets);

			foreach ($assets as $index => $path) {
				if (Url::isAbsolute($path) === true) {
					$customs['custom-' . $index] = $path;
					continue;
				}

				$asset = new Asset($path);

				if ($asset->exists() === true) {
					$customs['custom-' . $index] =  $asset->url() . '?' . $asset->modified();
				}
			}
		}

		return $customs;
	}

	/**
	 * Generates an array with all assets
	 * that need to be loaded for the panel (js, css, icons)
	 */
	public function external(): array
	{
		return [
			'css'         => $this->css(),
			'icons'       => $this->favicons(),
			'import-maps' => $this->importMaps(),
			'js'          => $this->js(),
		];
	}

	/**
	 * Returns array of favicon icons based on config option
	 *
	 * @throws \Infinri\Exception\InvalidArgumentException
	 */
	public function favicons(): array
	{
		$icons = $this->infinri->option('panel.favicon', [
			[
				'rel'   => 'apple-touch-icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/apple-touch-icon.png'
			],
			[
				'rel'   => 'alternate icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/favicon.png'
			],
			[
				'rel'   => 'shortcut icon',
				'type'  => 'image/svg+xml',
				'href'  => $this->url . '/favicon.svg'
			],
			[
				'rel'   => 'apple-touch-icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/apple-touch-icon-dark.png',
				'media' => '(prefers-color-scheme: dark)'
			],
			[
				'rel'   => 'alternate icon',
				'type'  => 'image/png',
				'href'  => $this->url . '/favicon-dark.png',
				'media' => '(prefers-color-scheme: dark)'
			]
		]);

		if (is_array($icons) === true) {
			// normalize options
			foreach ($icons as &$icon) {
				$icon['href']  = Url::to($icon['href']);
				$icon['nonce'] = $this->nonce;
			}

			return array_values($icons);
		}

		// make sure to convert favicon string to array
		if (is_string($icons) === true) {
			return [
				[
					'rel'   => 'shortcut icon',
					'type'  => F::mime($icons),
					'href'  => Url::to($icons),
					'nonce' => $this->nonce
				]
			];
		}

		throw new InvalidArgumentException(
			message: 'Invalid panel.favicon option'
		);
	}

	/**
	 * Load the SVG icon sprite
	 * This will be injected in the
	 * initial HTML document for the Panel
	 */
	public function icons(): string
	{
		$dir   = $this->infinri->root('panel') . '/';
		$dir  .= $this->isDev ? 'public' : 'dist';
		$icons = F::read($dir . '/img/icons.svg');
		$icons = preg_replace('/<!--(.|\s)*?-->/', '', $icons);
		return $icons;
	}

	/**
	 * Get all import maps
	 */
	public function importMaps(): array
	{
		return array_filter([
			'vue' => $this->vue()
		]);
	}

	/**
	 * Get all js files
	 */
	public function js(): array
	{
		$js = [
			'vendor' => [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/js/vendor.min.js',
				'type'  => 'module'
			],
			'plugin-registry' => [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/js/plugins.js',
				'type'  => 'module'
			],
			'plugins' => [
				'nonce' => $this->nonce,
				'src'   => $this->plugins->url('js'),
				'defer' => true
			],
			...A::map($this->custom('panel.js'), fn ($src) => [
				'nonce' => $this->nonce,
				'src'   => $src,
				'type'  => 'module',
				'defer' => true
			]),
			'index' => [
				'src'  => $this->url . '/js/index.min.js',
				'type' => 'module'
			],
		];


		// During dev mode, add vite client and adapt
		// path to `index.js` - vendor does not need
		// to be loaded in dev mode
		if ($this->isDev === true) {
			// Load the non-minified index.js, remove vendor script
			$js['index']['src']  = $this->url . '/src/index.js';
			$js['vendor'] = null;

			// Add vite dev client
			$js['vite'] = [
				'nonce' => $this->nonce,
				'src'   => $this->url . '/@vite/client',
				'type'  => 'module'
			];
		}

		return array_filter($js);
	}

	/**
	 * Links all dist files in the media folder
	 * and returns the link to the requested asset
	 *
	 * @throws \Infinri\Exception\Exception If Panel assets could not be moved to the public directory
	 */
	public function link(): bool
	{
		$mediaRoot   = $this->infinri->root('media') . '/panel';
		$panelRoot   = $this->infinri->root('panel') . '/dist';
		$versionHash = $this->infinri->versionHash();
		$versionRoot = $mediaRoot . '/' . $versionHash;

		// check if the version already exists
		if (is_dir($versionRoot) === true) {
			return false;
		}

		// delete the panel folder and all previous versions
		Dir::remove($mediaRoot);

		// recreate the panel folder
		Dir::make($mediaRoot, true);

		// copy assets to the dist folder
		if (Dir::copy($panelRoot, $versionRoot) !== true) {
			throw new Exception(
				message: 'Panel assets could not be linked'
			);
		}

		return true;
	}

	/**
	 * Get the base URL for all assets depending on dev mode
	 */
	public function url(): string
	{
		// vite is not running, use production assets
		if ($this->isDev === false) {
			return $this->infinri->url('media') . '/panel/' . $this->infinri->versionHash();
		}

		// explicitly configured base URL
		$dev = $this->infinri->option('panel.dev');

		if (is_string($dev) === true) {
			return $dev;
		}

		// port 3000 of the current Infinri request
		return rtrim($this->infinri->request()->url([
			'port'   => 3000,
			'path'   => null,
			'params' => null,
			'query'  => null
		])->toString(), '/');
	}

	/**
	 * Get the correct Vue script URL depending on dev mode
	 * and the enabled/disabled template compiler
	 */
	public function vue(): string
	{
		// During dev mode, load the dev version of Vue
		if ($this->isDev === true) {
			return $this->url . '/node_modules/vue/dist/vue.esm.browser.js';
		}

		if ($this->infinri->option('panel.vue.compiler', true) === true) {
			return $this->url . '/js/vue.esm.browser.min.js';
		}

		return $this->url . '/js/vue.runtime.esm.min.js';
	}
}
