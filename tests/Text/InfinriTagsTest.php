<?php

namespace Infinri\Text;

use Exception;
use Infinri\Cms\App;
use Infinri\Toolkit\Html;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Filesystem\Dir;
use Infinri\Filesystem\F;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(InfinriTags::class)]
class InfinriTagsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = INFINRI_TMP_DIR . '/Text.InfinriTags';

	protected array $gistDomains = [];

	protected function setUp(): void
	{
		$this->gistDomains = Html::$gistDomains;
		Html::$gistDomains = ['gist.example.com'];

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Html::$gistDomains = $this->gistDomains;
		Dir::remove(static::TMP);
	}

	public static function dataProvider(): array
	{
		$tests = [];

		foreach (Dir::read($root = static::FIXTURES . '/infinritext') as $dir) {
			$infinritext = F::read($root . '/' . $dir . '/test.txt');
			$expected  = F::read($root . '/' . $dir . '/expected.html');

			$tests[] = [trim($infinritext), trim($expected)];
		}

		return $tests;
	}

	public function testParse(): void
	{
		InfinriTag::$types = [
			'test' => [
				'html' => fn () => 'test'
			]
		];

		$this->assertSame('test', InfinriTags::parse('(test: foo)'));
		$this->assertSame('test', InfinriTags::parse('(Test: foo)'));
		$this->assertSame('test', InfinriTags::parse('(TEST: foo)'));
		$this->assertSame('test', InfinriTags::parse('(tEsT: foo)'));
	}

	public function testParseWithValue(): void
	{
		InfinriTag::$types = [
			'test' => [
				'html' => fn ($tag) => $tag->value
			]
		];

		$this->assertSame('foo', InfinriTags::parse('(test: foo)'));
		$this->assertSame('foo', InfinriTags::parse('(Test: foo)'));
		$this->assertSame('foo', InfinriTags::parse('(TEST: foo)'));
	}

	public function testParseWithAttribute(): void
	{
		InfinriTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => fn ($tag) => $tag->value . '|' . $tag->a
			]
		];

		$this->assertSame('foo|bar', InfinriTags::parse('(test: foo a: bar)'));
		$this->assertSame('foo|bar', InfinriTags::parse('(Test: foo A: bar)'));
		$this->assertSame('foo|bar', InfinriTags::parse('(TEST: foo a: bar)'));
	}

	public function testParseWithException(): void
	{
		InfinriTag::$types = [
			'test' => [
				'html' => fn () => throw new Exception('Just for fun')
			],
			'invalidargument' => [
				'html' => fn () => throw new InvalidArgumentException(
					message: 'Just for fun'
				)
			],
			'undefined' => [
				'html' => fn () => throw new InvalidArgumentException(
					message: 'Undefined tag type: undefined'
				)
			]
		];

		ErrorLog::$log = '';
		$this->assertSame('(test: foo)', InfinriTags::parse('(test: foo)'));
		$this->assertStringContainsString('Exception: Just for fun', ErrorLog::$log);

		ErrorLog::$log = '';
		$this->assertSame('(invalidargument: foo)', InfinriTags::parse('(invalidargument: foo)'));
		$this->assertStringContainsString('Infinri\Exception\InvalidArgumentException: Just for fun', ErrorLog::$log);

		ErrorLog::$log = '';
		$this->assertSame('(undefined: foo)', InfinriTags::parse('(undefined: foo)'));
		$this->assertSame('', ErrorLog::$log);
	}

	public function testParseWithExceptionDebug1(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Just for fun');

		InfinriTag::$types = [
			'test' => [
				'html' => fn () => throw new Exception('Just for fun')
			]
		];

		InfinriTags::parse('(test: foo)', [], debug: true);
	}

	public function testParseWithExceptionDebug2(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Just for fun');

		InfinriTag::$types = [
			'invalidargument' => [
				'html' => fn () => throw new InvalidArgumentException(message: 'Just for fun')
			]
		];

		InfinriTags::parse('(invalidargument: foo)', [], debug: true);
	}

	public function testParseWithExceptionDebug3(): void
	{
		InfinriTag::$types = [
			'undefined' => [
				'html' => fn () => throw new InvalidArgumentException(
					message: 'Undefined tag type: undefined'
				)
			]
		];

		$this->assertSame('(undefined: foo)', InfinriTags::parse('(undefined: foo)', [], debug: true));
	}

	public function testParseWithoutOptionsArgument(): void
	{
		$parameters = array_map(
			fn ($parameter) => $parameter->getName(),
			(new \ReflectionMethod(InfinriTags::class, 'parse'))->getParameters()
		);

		$this->assertSame(['text', 'data', 'debug'], $parameters);
	}

	public function testParseWithBrackets(): void
	{
		InfinriTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => function ($tag) {
					$value = $tag->value;

					if (empty($tag->a) === false) {
						$value .= ' - ' . $tag->a;
					}

					return $value;
				}
			]
		];

		$this->assertSame('foo(bar)', InfinriTags::parse('(test: foo(bar))'));
		$this->assertSame('foo(bar) - hello(world)', InfinriTags::parse('(test: foo(bar) a: hello(world))'));
		$this->assertSame('foo(bar) hello', InfinriTags::parse('(test: foo(bar) hello)'));
		$this->assertSame('foo(bar hello(world))', InfinriTags::parse('(test: foo(bar hello(world)))'));
		$this->assertSame('foo - (bar)', InfinriTags::parse('(test: foo a: (bar))'));
		$this->assertSame('(bar)', InfinriTags::parse('(test: (bar))'));
		// will not parse if brackets don't match
		$this->assertSame('(test: foo (bar)', InfinriTags::parse('(test: foo (bar)'));
	}

	#[DataProvider('dataProvider')]
	public function testWithMarkdown(
		string $infinritext,
		string $expected
	): void {
		$infinri = $this->app->clone([
			'options' => [
				'markdown' => [
					'extra' => false
				]
			]
		]);

		$this->assertSame($expected, $infinri->infinritext($infinritext));
	}

	#[DataProvider('dataProvider')]
	public function testWithMarkdownExtra(
		string $infinritext,
		string $expected
	): void {
		$infinri = $this->app->clone([
			'options' => [
				'markdown' => [
					'extra' => true
				]
			]
		]);

		$this->assertSame($expected, $infinri->infinritext($infinritext));
	}

	public function testHooks(): void
	{
		$app = $this->app->clone([
			'hooks' => [
				'infinritags:before' => fn ($text, $data, $options) => 'before'
			]
		]);

		$this->assertSame('before', $app->infinritags('test'));

		$app = $app->clone([
			'hooks' => [
				'infinritags:after' => fn ($text, $data, $options) => 'after'
			]
		]);

		$this->assertSame('after', $app->infinritags('test'));
	}

	public static function globalOptionsProvider(): array
	{
		return [
			[
				'(image: image.jpg link: https://infinri.com/)',
				'<figure><a href="https://infinri.com/" rel="nofollow"><img alt="" class="image-class" src="/image.jpg"></a></figure>'
			],
			[
				'(link: http://wikipedia.org text: Wikipedia)',
				'<p><a class="link-class" href="http://wikipedia.org" rel="noreferrer" target="_blank">Wikipedia</a></p>'
			],
			[
				'(tel: +49123456789)',
				'<p><a class="phone" href="tel:+49123456789">+49123456789</a></p>'
			],
			[
				'(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)',
				'<figure class="video-class"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg"></iframe></figure>'
			]
		];
	}

	#[DataProvider('globalOptionsProvider')]
	public function testGlobalOptions(
		string $infinritext,
		string $expected
	): void {
		$infinri = $this->app->clone([
			'options' => [
				'infinritext' => [
					'image' => [
						'rel' => 'nofollow',
						'imgclass' => 'image-class'
					],
					'link' => [
						'class' => 'link-class',
						'target' => '_blank'
					],
					'tel' => [
						'class' => 'phone'
					],
					'video' => [
						'class' => 'video-class',
					]
				]
			]
		]);

		$this->assertSame($expected, $infinri->infinritext($infinritext));
	}
}
