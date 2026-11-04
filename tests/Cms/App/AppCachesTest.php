<?php

namespace Infinri\Cms;

use Infinri\Cache\FileCache;
use Infinri\Cache\NullCache;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Filesystem\Dir;

class AppCachesTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.AppCaches';

	public function app(array $props = [])
	{
		return new App([
			'roots' => [
				'index' => static::TMP,
			],
			...$props
		]);
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		Dir::remove(static::TMP);
	}

	public function testDisabledCache(): void
	{
		$this->assertInstanceOf(NullCache::class, $this->app()->cache('pages'));
	}

	public function testEnabledCacheWithoutOptions(): void
	{
		$infinri = $this->app([
			'options' => [
				'cache.pages' => true
			]
		]);

		$this->assertInstanceOf(FileCache::class, $infinri->cache('pages'));
		$this->assertSame($infinri->root('cache'), $infinri->cache('pages')->options()['root']);
	}

	public function testInvalidCacheType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid cache type "not-exists"');

		$infinri = $this->app([
			'options' => [
				'cache.pages' => [
					'type' => 'not-exists'
				]
			]
		]);

		$infinri->cache('pages');
	}

	public function testEnabledCacheWithOptions(): void
	{
		$infinri = $this->app([
			'urls' => [
				'index' => 'https://infinri.com/test'
			],
			'options' => [
				'cache.pages' => [
					'type' => 'file',
					'root' => $root = static::TMP . '/cache'
				]
			]
		]);

		$this->assertInstanceOf(FileCache::class, $infinri->cache('pages'));
		$this->assertSame($root, $infinri->cache('pages')->options()['root']);

		$infinri->cache('pages')->set('home', 'test');
		$this->assertFileExists($root . '/infinri.com_test/pages/home.cache');
	}

	public function testEnabledCacheWithOptionsAndPortPrefix(): void
	{
		$infinri = $this->app([
			'urls' => [
				'index' => 'http://127.0.0.1:8000'
			],
			'options' => [
				'cache.pages' => [
					'type' => 'file',
					'root' => $root = static::TMP . '/cache'
				]
			]
		]);

		$this->assertInstanceOf(FileCache::class, $infinri->cache('pages'));
		$this->assertSame($root, $infinri->cache('pages')->options()['root']);

		$infinri->cache('pages')->set('home', 'test');
		$this->assertFileExists($root . '/127.0.0.1_8000/pages/home.cache');
	}

	public function testPluginDefaultCache(): void
	{
		App::plugin('developer/plugin', [
			'options' => [
				'cache' => true
			]
		]);

		$this->assertInstanceOf(FileCache::class, $this->app()->cache('developer.plugin'));
	}

	public function testPluginCustomCache(): void
	{
		App::plugin('developer/plugin', [
			'options' => [
				'cache.api' => true
			]
		]);

		$this->assertInstanceOf(FileCache::class, $this->app()->cache('developer.plugin.api'));
	}

	public function testDefaultCacheTypeClasses(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$types = $app->extensions('cacheTypes');

		foreach ($types as $className) {
			$this->assertTrue(class_exists($className));
		}
	}
}
