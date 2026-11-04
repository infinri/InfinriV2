<?php

namespace Infinri\Plugin;

use Infinri\Cms\TestCase;
use Infinri\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Asset::class)]
class AssetTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/plugin-assets';
	public const TMP      = INFINRI_TMP_DIR . '/Plugin.Asset';

	protected Plugin $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		Dir::copy(static::FIXTURES, static::TMP . '/test-plugin');

		$this->plugin = new Plugin(
			name: 'infinri/test-plugin',
			root: static::TMP . '/test-plugin'
		);

		touch(static::TMP . '/test-plugin/assets/test.css', 1337000000);
	}

	public function testPublishAtIsRemoved(): void
	{
		$this->assertFalse(method_exists(Asset::class, 'publishAt'));
	}

	public function testExtension(): void
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame('css', $asset->extension());
	}

	public function testFilename(): void
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame('test.css', $asset->filename());
	}

	public function testMedia(): void
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame('3526409702-1337000000', $asset->mediaHash());
		$this->assertSame($this->plugin->mediaRoot() . '/3526409702-1337000000/test.css', $asset->mediaRoot());
		$this->assertSame($url = '/media/plugins/infinri/test-plugin/3526409702-1337000000/test.css', $asset->mediaUrl());
		$this->assertSame($url, $asset->url());
		$this->assertSame($url, (string)$asset);
	}

	public function testModified(): void
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame(1337000000, $asset->modified());
	}

	public function testPathRoot(): void
	{
		$asset = new Asset(
			$path = 'test.css',
			$root = static::TMP . '/test-plugin/assets/test.css',
			$plugin = $this->plugin
		);

		$this->assertSame($path, $asset->path());
		$this->assertSame($plugin, $asset->plugin());
		$this->assertSame($root, $asset->root());
	}

	public function testPublish(): void
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertFileDoesNotExist($asset->mediaRoot());
		$asset->publish();
		$this->assertFileExists($asset->mediaRoot());
	}
}
