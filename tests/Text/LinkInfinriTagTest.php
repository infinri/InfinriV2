<?php

namespace Infinri\Text;

use Infinri\Cms\App;
use Infinri\Filesystem\Dir;
use Infinri\TestCase;

class LinkInfinriTagTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Text.LinkInfinriTag';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testWithLangAttribute(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame('<a href="https://infinri.com/en/a">infinri.com/en/a</a>', $app->infinritags('(link: a lang: en)'));
		$this->assertSame('<a href="https://infinri.com/de/a">infinri.com/de/a</a>', $app->infinritags('(link: a lang: de)'));
	}

	public function testWithHash(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame('<a href="https://infinri.com/en/a">infinri.com/en/a</a>', $app->infinritags('(link: a)'));
		$this->assertSame('<a href="https://infinri.com/de/a">infinri.com/de/a</a>', $app->infinritags('(link: a lang: de)'));
		$this->assertSame('<a href="https://infinri.com/en/a#anchor">infinri.com/en/a</a>', $app->infinritags('(link: a#anchor lang: en)'));
		$this->assertSame('<a href="https://infinri.com/de/a#anchor">infinri.com/de/a</a>', $app->infinritags('(link: a#anchor lang: de)'));
	}

	public function testWithUuid(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'foo.jpg',
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			]
		]);

		$result = $app->infinritags('(link: page://page-uuid)');
		$this->assertSame('<a href="https://infinri.com/a">infinri.com/a</a>', $result);

		// broken link without text: dropped entirely
		$result = $app->infinritags('(link: page://not-exists)');
		$this->assertSame('', $result);

		$result = $app->infinritags('(link: file://file-uuid text: file)');
		$this->assertSame('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);

		// broken link with text: link dropped, text kept
		$result = $app->infinritags('(link: file://not-exists text: file)');
		$this->assertSame('<span>file</span>', $result);
	}

	public function testWithUuidDebug(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'foo.jpg',
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			],
			'options' => [
				'debug' => true
			]
		]);

		$result = $app->infinritags('(link: page://not-exists)');
		$this->assertSame('<span class="infinri-broken-link">🚨 The link &quot;page://not-exists&quot; cannot be found</span>', $result);

		// a custom class is merged with the broken-link class
		$result = $app->infinritags('(link: page://not-exists class: my-link)');
		$this->assertSame('<span class="infinri-broken-link my-link">🚨 The link &quot;page://not-exists&quot; cannot be found</span>', $result);
	}

	public function testWithUuidDebugText(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'foo.jpg',
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			],
			'options' => [
				'debug' => true
			]
		]);

		$result = $app->infinritags('(link: page://not-exists text: click here)');
		$this->assertSame('<span class="infinri-broken-link">🚨 The link &quot;page://not-exists&quot; cannot be found for the link text &quot;click here&quot;</span>', $result);
	}

	public function testWithUuidAndLang(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_DE',
					'url'     => '/de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							[
								'filename'     => 'foo.jpg',
								'translations' => [
									[
										'code' => 'en',
										'content' => ['uuid' => 'file-uuid']
									],
									[
										'code' => 'de',
										'content' => []
									]
								]
							]
						],
						'translations' => [
							[
								'code' => 'en',
								'content' => ['uuid' => 'page-uuid']
							],
							[
								'code' => 'de',
								'content' => ['slug' => 'ae']
							]
						]
					]
				]
			]
		]);

		$result = $app->infinritags('(link: page://page-uuid lang: de)');
		$this->assertSame('<a href="https://infinri.com/de/ae">infinri.com/de/ae</a>', $result);

		$result = $app->infinritags('(link: file://file-uuid text: file lang: de)');
		$this->assertSame('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);
	}
}
