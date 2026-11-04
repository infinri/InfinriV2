<?php

namespace Infinri\Panel\Areas;

use Infinri\Cms\App;
use Infinri\Cms\System\UpdateStatus;
use Infinri\Http\Cookie;

class SystemTest extends AreaTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/SystemTest';

	protected static string $host;

	public static function setUpBeforeClass(): void
	{
		static::$host = UpdateStatus::$host;
		UpdateStatus::$host = 'file://' . static::FIXTURES;
	}

	public static function tearDownAfterClass(): void
	{
		UpdateStatus::$host = static::$host;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->app([
			'options' => [
				'updates' => true,
				'url'     => 'https://example.com'
			]
		]);
		$this->install();
	}

	protected function tearDown(): void
	{
		Cookie::$key = 'InfinriHttpCookieKey';
		parent::tearDown();
	}

	protected function compilerWarning(): array
	{
		return [
			'id'    => 'vue-compiler',
			'link'  => 'https://infinri.com/security/vue-compiler',
			'text'  => 'The Vue template compiler is enabled',
			'theme' => 'notice'
		];
	}

	protected function contentSaltWarning(): array
	{
		return [
			'id'    => 'content-salt',
			'link'  => 'https://infinri.com/security/content-salt',
			'text'  => 'The content salt has not been changed from its default value',
			'theme' => 'notice'
		];
	}

	protected function cookieKeyWarning(): array
	{
		return [
			'id'    => 'cookie-key',
			'link'  => 'https://infinri.com/security/cookie-key',
			'text'  => 'The cookie signing key has not been changed from its default value',
			'theme' => 'notice'
		];
	}

	protected function customWarning(): array
	{
		return [
			'text'  => 'This is a very important announcement!',
			'infinri' => '*',
			'php'   => '*'
		];
	}

	public function testLicenseDialogsAreNotRegistered(): void
	{
		$this->login();

		$areas = \Infinri\Panel\Panel::areas();

		$this->assertArrayHasKey('system', $areas);
		$this->assertArrayNotHasKey('dialogs', $areas['system']);
		$this->assertSame([], \Infinri\Panel\Panel::routesForDialogs('system', $areas['system']));
	}

	public function testViewWithoutAuthentication(): void
	{
		$this->assertRedirect('system', 'login');
	}

	public function testView(): void
	{
		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame('system', $view['id']);
		$this->assertSame('System', $view['title']);
		$this->assertSame('k-system-view', $view['component']);
		$this->assertSame([
			[
				'label' => 'Free update 88888.8.8 available',
				'value' => $this->app->version(),
				'link'  => 'https://infinri.com/releases/88888.8.8',
				'theme' => 'info',
				'icon'  => 'info'
			],
			[
				'label' => 'PHP',
				'value' => phpversion(),
				'icon'  => 'code'
			],
			[
				'label' => 'Server',
				'value' => 'php',
				'icon'  => 'server'
			],
		], $props['environment']);
		$this->assertCount(3, $props['environment']);
		$this->assertSame(['Free update 88888.8.8 available', 'PHP', 'Server'], array_column($props['environment'], 'label'));
		$this->assertSame([], $props['exceptions']);
		$this->assertSame([], $props['plugins']);
		$this->assertSame([
			[
				'text'  => 'This is a very important announcement!',
				'infinri' => '*',
				'php'   => '*'
			],
			[
				'id'    => 'vue-compiler',
				'link' => 'https://infinri.com/security/vue-compiler',
				'text' => 'The Vue template compiler is enabled',
				'theme' => 'notice',
			],
			$this->contentSaltWarning(),
			$this->cookieKeyWarning()
		], $props['security']);
		$this->assertSame([
			'content' => 'https://example.com/content/site.txt',
			'git'     => null,
			'infinri'   => null,
			'site'    => 'https://example.com/site'
		], $props['urls']);
	}

	public function testViewLocal(): void
	{
		$this->app([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([], $props['exceptions']);
		$this->assertSame([
			$this->customWarning(),
			[
				'id'    => 'local',
				'icon'  => 'info',
				'theme' => 'info',
				'text'  => 'The site is running locally with relaxed security checks'
			],
			$this->compilerWarning(),
			$this->contentSaltWarning(),
			$this->cookieKeyWarning()
		], $props['security']);
	}

	public function testViewDebug(): void
	{
		$this->app([
			'options' => [
				'debug' => true
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([], $props['exceptions']);
		$this->assertSame([
			$this->customWarning(),
			[
				'id'    => 'debug',
				'icon'  => 'alert',
				'theme' => 'negative',
				'text'  => 'Debugging must be turned off in production',
				'link'  => 'https://infinri.com/security/debug'
			],
			$this->compilerWarning(),
			$this->contentSaltWarning(),
			$this->cookieKeyWarning()
		], $props['security']);
	}

	public function testViewWithoutConfiguredTemplateCompiler(): void
	{
		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayHasKey(1, $props['security']);
		$this->assertSame($this->compilerWarning(), $props['security'][1]);
	}

	public function testViewWithConfiguredTemplateCompiler(): void
	{
		$this->app([
			'options' => [
				'panel.vue.compiler' => true
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([
			$this->customWarning(),
			$this->contentSaltWarning(),
			$this->cookieKeyWarning()
		], $props['security']);
	}

	public function testViewWithoutContentSalt(): void
	{
		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayHasKey(2, $props['security']);
		$this->assertSame($this->contentSaltWarning(), $props['security'][2]);
	}

	public function testViewWithContentSalt(): void
	{
		$this->app([
			'options' => [
				'content.salt' => 'custom-salt'
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([
			$this->customWarning(),
			$this->compilerWarning(),
			$this->cookieKeyWarning()
		], $props['security']);
	}

	public function testViewWithoutCustomCookieKey(): void
	{
		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayHasKey(3, $props['security']);
		$this->assertSame($this->cookieKeyWarning(), $props['security'][3]);
	}

	public function testViewWithCustomCookieKey(): void
	{
		Cookie::$key = 'custom-cookie-key';

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayNotHasKey(3, $props['security']);
	}

	public function testViewWithCustomCookieKeyInConfig(): void
	{
		$this->app([
			'options' => [
				'cookie' => [
					'key' => 'custom-cookie-key'
				]
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayNotHasKey(3, $props['security']);
	}

	public function testViewHttps(): void
	{
		$this->app([
			'options' => [
				'url' => 'http://example.com'
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([
			$this->customWarning(),
			[
				'id'   => 'https',
				'text' => 'We recommend HTTPS for all your sites',
				'link' => 'https://infinri.com/security/https'
			],
			$this->compilerWarning(),
			$this->contentSaltWarning(),
			$this->cookieKeyWarning()
		], $props['security']);
	}

	public function testViewWithPlugins(): void
	{
		App::plugin(
			name: 'infinri/private',
			extends: [],
			info: []
		);

		App::plugin(
			name: 'infinri/public',
			extends: [],
			info: [
				'authors' => [
					[
						'name' => 'A'
					],
					[
						'name' => 'B'
					]
				],
				'homepage' => 'https://infinri.com',
				'version'  => '1.0.0',
			]
		);

		App::plugin(
			name: 'infinri/unknown',
			extends: [],
			info: [
				'version' => '1.0.0'
			]
		);

		$this->login();

		$view     = $this->view('system');
		$expected = [
			[
				'author'  => '–',
				'name'    => [
					'text' => 'infinri/private',
					'href' => null
				],
				'version' => [
					'currentVersion' => '?',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'infinri/private',
					'theme' => 'passive',
					'url' => null
				]
			],
			[
				'author'  => 'A, B',
				'name'    => [
					'text' => 'infinri/public',
					'href' => 'https://plugins.infinri.com/infinri/public'
				],
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'info',
					'label' => 'Free update 88888.8.8 available',
					'latestVersion' => '99999.9.9',
					'pluginName' => 'infinri/public',
					'theme' => 'info',
					'url' => 'https://example.com/public-plugin/releases/tag/88888.8.8'
				]
			],
			[
				'author'  => '–',
				'name'    => [
					'text' => 'infinri/unknown',
					'href' => null
				],
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'infinri/unknown',
					'theme' => 'passive',
					'url' => null
				]
			]
		];

		$this->assertSame($expected, $view['props']['plugins']);
		$this->assertSame([], $view['props']['exceptions']);

		foreach ($view['props']['plugins'] as $row) {
			$this->assertSame(['author', 'name', 'version'], array_keys($row));
			$this->assertArrayNotHasKey('license', $row);
			$this->assertArrayNotHasKey('status', $row);
		}
	}

	public function testViewWithPluginsDebug(): void
	{
		App::plugin(
			name: 'infinri/private',
			extends: [],
			info: []
		);

		App::plugin(
			name: 'infinri/public',
			extends: [],
			info: [
				'authors' => [
					[
						'name' => 'A'
					],
					[
						'name' => 'B'
					]
				],
				'homepage' => 'https://infinri.com',
				'version'  => '1.0.0',
			]
		);

		App::plugin(
			name: 'infinri/unknown',
			extends: [],
			info: [
				'version' => '1.0.0'
			]
		);

		$this->app([
			'options' => [
				'debug' => true
			]
		]);

		$this->login();

		$view     = $this->view('system');
		$expected = [
			[
				'author'  => '–',
				'name'    => [
					'text' => 'infinri/private',
					'href' => null
				],
				'version' => [
					'currentVersion' => '?',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'infinri/private',
					'theme' => 'passive',
					'url' => null
				]
			],
			[
				'author'  => 'A, B',
				'name'    => [
					'text' => 'infinri/public',
					'href' => 'https://plugins.infinri.com/infinri/public'
				],
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'info',
					'label' => 'Free update 88888.8.8 available',
					'latestVersion' => '99999.9.9',
					'pluginName' => 'infinri/public',
					'theme' => 'info',
					'url' => 'https://example.com/public-plugin/releases/tag/88888.8.8'
				]
			],
			[
				'author'  => '–',
				'name'    => [
					'text' => 'infinri/unknown',
					'href' => null
				],
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'infinri/unknown',
					'theme' => 'passive',
					'url' => null
				]
			]
		];

		$this->assertSame($expected, $view['props']['plugins']);

		// curl's wording for a missing local file differs between
		// versions ("Couldn't open file" vs. "Could not open file")
		$this->assertSame([
			'Could not load update data for plugin infinri/private: Couldn\'t open file ' .
			static::FIXTURES . '/plugins/infinri/private.json',
			'Could not load update data for plugin infinri/unknown: Couldn\'t open file ' .
			static::FIXTURES . '/plugins/infinri/unknown.json',
		], str_replace(
			'Could not open file',
			'Couldn\'t open file',
			$view['props']['exceptions']
		));
	}

	public function testViewWithoutUpdateCheck(): void
	{
		$this->app([
			'options' => [
				'content.salt'       => 'custom-salt',
				'panel.vue.compiler' => true,
				'updates'            => false,
			]
		]);

		Cookie::$key = 'custom-cookie-key';

		App::plugin(
			name: 'infinri/public',
			extends: [],
			info: [
				'authors' => [
					[
						'name' => 'A'
					],
					[
						'name' => 'B'
					]
				],
				'homepage' => 'https://infinri.com',
				'version'  => '1.0.0',
			]
		);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([
			[
				'label' => 'Version',
				'value' => $this->app->version(),
				'link'  => null,
				'theme' => null,
				'icon'  => 'info'
			],
			[
				'label' => 'PHP',
				'value' => phpversion(),
				'icon'  => 'code'
			],
			[
				'label' => 'Server',
				'value' => 'php',
				'icon'  => 'server'
			],
		], $props['environment']);

		$this->assertSame([], $props['security']);

		$this->assertSame([
			[
				'author'  => 'A, B',
				'name'    => [
					'text' => 'infinri/public',
					'href' => 'https://infinri.com'
				],
				'version' => '1.0.0'
			]
		], $props['plugins']);
		$this->assertSame([], $props['exceptions']);
	}
}
