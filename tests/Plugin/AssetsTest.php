<?php

namespace Infinri\Plugin;

use Infinri\Cms\App;
use Infinri\Filesystem\Dir;
use Infinri\Filesystem\F;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Assets::class)]
class AssetsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/plugin-assets';
	public const TMP      = INFINRI_TMP_DIR . '/Plugin.Assets';

	protected function setUp(): void
	{
		$a = static::TMP . '/site/plugins/a';
		F::write($a . '/index.php', '<?php Infinri::plugin("infinri/a", []);');
		F::write($a . '/assets/test.css', 'test');

		$b = static::TMP . '/site/plugins/b';
		F::write($b . '/index.php', '<?php Infinri::plugin("infinri/b", ["assets" => ["' . $b . '/foo/bar.css"]]);');
		F::write($b . '/foo/bar.css', 'test');

		$c = static::TMP . '/site/plugins/c';
		F::write($c . '/index.php', '<?php Infinri::plugin("infinri/c", ["assets" => ["test.css" => "' . $c . '/foo/bar.css"]]);');
		F::write($c . '/foo/bar.css', 'test');

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testClean(): void
	{
		// create orphans
		F::write(
			$a = static::TMP . '/media/plugins/infinri/a/orphan.css',
			'test'
		);
		F::write(
			$b = static::TMP . '/media/plugins/infinri/a/assets/orphan.css',
			'test'
		);

		$this->assertFileExists($a);
		$this->assertFileExists($b);

		Assets::clean('infinri/a');

		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
	}

	public function testCleanRemovesLegacyUnhashedPaths(): void
	{
		touch(static::TMP . '/site/plugins/b/foo/bar.css', 1337000000);

		F::write(
			$legacy = static::TMP . '/media/plugins/infinri/b/foo/bar.css',
			'test'
		);

		$this->assertFileExists($legacy);

		Assets::clean('infinri/b');

		$this->assertFileDoesNotExist($legacy);
	}

	public function testCss(): void
	{
		// assets defined in plugin config
		$plugin = new Plugin(
			name: 'infinri/test-plugin',
			extends: [
				'assets' => [
					'test.css' => static::TMP . '/test.css',
					'test.js'  => static::TMP . '/test.js'
				]
			],
			root: static::TMP
		);

		$assets = Assets::factory($plugin);
		$this->assertCount(2, $assets);
		$this->assertCount(1, $assets->css());
		$this->assertSame('test.css', $assets->css()->first()->path());
		$this->assertCount(1, $assets->js());
		$this->assertSame('test.js', $assets->js()->first()->path());
	}

	public function testFactory(): void
	{
		// assets defined in plugin config
		$plugin = new Plugin(
			name: 'infinri/test-plugin',
			extends: [
				'assets' => [
					'c.css' => static::FIXTURES . '/a.css',
					'd.css' => static::FIXTURES . '/foo/b.css'
				]
			],
			root: static::FIXTURES
		);

		$assets = Assets::factory($plugin);
		$this->assertInstanceOf(Assets::class, $assets);
		$this->assertSame(2, $assets->count());
		$this->assertSame(static::FIXTURES . '/a.css', $assets->get('c.css')->root());
		$this->assertSame(static::FIXTURES . '/foo/b.css', $assets->get('d.css')->root());

		// assets defined as non-associative array in the plugin config
		$plugin = new Plugin(
			name: 'infinri/test-plugin',
			extends: [
				'assets' => [
					static::FIXTURES . '/a.css',
					static::FIXTURES . '/foo/b.css'
				]
			],
			root: static::FIXTURES
		);

		$assets = Assets::factory($plugin);
		$this->assertInstanceOf(Assets::class, $assets);
		$this->assertSame(2, $assets->count());
		$this->assertSame(static::FIXTURES . '/a.css', $assets->get('a.css')->root());
		$this->assertSame(static::FIXTURES . '/foo/b.css', $assets->get('foo/b.css')->root());

		// assets defined als closure in the plugin config
		$plugin = new Plugin(
			name: 'infinri/test-plugin',
			extends: [
				'assets' => fn () => [
					static::FIXTURES . '/a.css',
					static::FIXTURES . '/foo/b.css'
				]
			],
			root: static::FIXTURES
		);

		$assets = Assets::factory($plugin);
		$this->assertInstanceOf(Assets::class, $assets);
		$this->assertSame(2, $assets->count());
		$this->assertSame(static::FIXTURES . '/a.css', $assets->get('a.css')->root());
		$this->assertSame(static::FIXTURES . '/foo/b.css', $assets->get('foo/b.css')->root());

		// assets gathered from `assets` folder inside plugin root
		$plugin = new Plugin(
			name: 'infinri/test-plugin',
			root: static::FIXTURES
		);

		$assets = Assets::factory($plugin);
		$this->assertInstanceOf(Assets::class, $assets);
		$this->assertSame(1, $assets->count());
		$this->assertSame(static::FIXTURES . '/assets/test.css', $assets->get('test.css')->root());
	}

	public function testPlugin(): void
	{
		$plugin = new Plugin(
			name: 'infinri/test-plugin',
			root: static::FIXTURES
		);

		$assets = Assets::factory($plugin);
		$this->assertSame($plugin, $assets->plugin());
	}

	public function testResolve(): void
	{
		touch(static::TMP . '/site/plugins/b/foo/bar.css', 1337000000);

		// right path and hash
		$media    = static::TMP . '/media/plugins/infinri/b/110971429-1337000000/foo/bar.css';
		$response = Assets::resolve(
			'infinri/b',
			'110971429-1337000000',
			'foo/bar.css'
		);

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());

		// wrong path
		$media    = static::TMP . '/media/plugins/infinri/b/110971429-1337000000/assets/foo.css';
		$response = Assets::resolve(
			'infinri/b',
			'110971429-1337000000',
			'assets/foo.css'
		);

		$this->assertNull($response);
		$this->assertFalse(is_link($media));

		// wrong hash
		// TODO: remove when media hash is enforced as mandatory
		// $media    = static::TMP . '/media/plugins/infinri/b/110971429-1337000000/foo/bar.css';
		// $response = PluginAssets::resolve(
		// 	'infinri/b',
		// 	'110971429-12345678',
		// 	'foo/bar.css'
		// );

		// $this->assertNull($response);
		// $this->assertFalse(is_link($media));

		// correct: different path and root
		touch(static::TMP . '/site/plugins/c/foo/bar.css', 1337000000);

		$media    = static::TMP . '/media/plugins/infinri/c/3526409702-1337000000/test.css';
		$response = Assets::resolve(
			'infinri/c',
			'3526409702-1337000000',
			'test.css'
		);

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());
	}

	public function testResolveWithoutHash(): void
	{
		touch(static::TMP . '/site/plugins/b/foo/bar.css', 1337000000);

		$media = static::TMP . '/media/plugins/infinri/b/foo/bar.css';

		$this->assertNull(Assets::resolve('infinri/b', '', 'foo/bar.css'));
		$this->assertFalse(is_link($media));
	}

	public function testResolveWithWrongHash(): void
	{
		touch(static::TMP . '/site/plugins/b/foo/bar.css', 1337000000);

		$media = static::TMP . '/media/plugins/infinri/b/110971429-12345678/foo/bar.css';

		$this->assertNull(Assets::resolve('infinri/b', '110971429-12345678', 'foo/bar.css'));
		$this->assertFalse(is_link($media));
	}

	public function testResolveAutomaticFromAssetsFolder(): void
	{
		touch(static::TMP . '/site/plugins/a/assets/test.css', 1337000000);

		$media    = static::TMP . '/media/plugins/infinri/a/3526409702-1337000000/test.css';
		$response = Assets::resolve(
			'infinri/a',
			'3526409702-1337000000',
			'test.css'
		);

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());


		$media    = static::TMP . '/media/plugins/infinri/a/3526409702-1337000000/assets/test.css';
		$response = Assets::resolve(
			'infinri/a',
			'3526409702-1337000000',
			'assets/test.css'
		);
		$this->assertNull($response);
		$this->assertFalse(is_link($media));
	}

	public function testAppCallInvalid(): void
	{
		$response = App::instance()->call('media/plugins/test/test/test.invalid');
		$this->assertNull($response);
	}
}
