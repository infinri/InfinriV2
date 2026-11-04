<?php

namespace Infinri\Text;

use Infinri\Cms\App;
use Infinri\Exception\BadMethodCallException;
use Infinri\Exception\InvalidArgumentException;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(InfinriTag::class)]
class InfinriTagTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Text.InfinriTag';

	protected function setUp(): void
	{
		InfinriTag::$types = [
			'test' => [
				'attr' => ['a', 'b'],
				'html' => fn ($tag) => 'test: ' . $tag->value . '-' . $tag->a . '-' . $tag->b
			],
			'noHtml' => [
				'attr' => ['a', 'b']
			],
			'invalidHtml' => [
				'attr' => ['a', 'b'],
				'html' => 'some string'
			],
			'file' => [
				'attr' => ['a'],
				'html' => 'some string'
			],
		];
	}

	protected function tearDown(): void
	{
		InfinriTag::$aliases = [];
		InfinriTag::$types = [];
		App::destroy();
	}

	public function testConstruct(): void
	{
		InfinriTag::$aliases = [];
		$tag = InfinriTag::parse('test: foo a: attrA b: attrB c: attrC');
		$this->assertSame('test', $tag->type);
		$this->assertSame('foo', $tag->test);
		$this->assertSame(['a' => 'attrA', 'b' => 'attrB c: attrC'], $tag->attrs);
		$this->assertSame([], $tag->data);
		$this->assertSame('foo', $tag->value);
	}

	public function testConstructAliasedTagType(): void
	{
		InfinriTag::$aliases = ['foo' => 'test'];
		$tag = InfinriTag::parse('foo: bar');
		$this->assertSame('test', $tag->type);
		$this->assertSame('bar', $tag->test);
	}

	public function testConstructMissingTagType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Undefined tag type: invalid');
		InfinriTag::parse('invalid: test');
	}

	public function test__call(): void
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$data = [
			'a' => 'dataA',
			'c' => 'dataC'
		];

		$tag = new InfinriTag('test', 'test value', $attr, $data);

		$this->assertSame('dataA', $tag->a());
		$this->assertSame('attrB', $tag->b());
		$this->assertSame('dataC', $tag->c());
	}

	public function test__callStatic(): void
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$result = InfinriTag::test('test value', $attr);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	public function testAttr(): void
	{
		$tag = new InfinriTag('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);

		// class properties
		$this->assertSame('attrA', $tag->a);
		$this->assertSame('attrB', $tag->b);

		// attr helper
		$this->assertSame('attrA', $tag->attr('a', 'fallback'));
		$this->assertSame('attrB', $tag->attr('b', 'fallback'));
	}

	public function testAttrFallback(): void
	{
		$tag = new InfinriTag('test', 'test value', [
			'a' => 'attrA'
		]);

		$this->assertNull($tag->b);
		$this->assertSame('fallback', $tag->attr('b', 'fallback'));
	}

	public function testFactory(): void
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$result = InfinriTag::factory('test', 'test value', $attr);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	public function testFile(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new InfinriTag('image', 'foo');
		$this->assertSame($file, $tag->file('a/a.jpg'));
	}

	public function testFileInParent(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new InfinriTag('image', 'foo', [], [
			'parent' => $page,
		]);
		$this->assertSame($file, $tag->file('a.jpg'));
	}

	public function testFileInFileParent(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new InfinriTag('image', 'foo', [], [
			'parent' => $file,
		]);
		$this->assertSame($file, $tag->file('a.jpg'));
	}

	public function testFileFromUuid(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => ['uuid' => 'image-uuid']
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new InfinriTag('image', 'foo');
		$this->assertSame($file, $tag->file('file://image-uuid'));

		// with parent
		$tag = new InfinriTag('image', 'foo', [], [
			'parent' => $page,
		]);
		$this->assertSame($file, $tag->file('file://image-uuid'));
	}

	public function testInfinri(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			]
		]);

		$tag = new InfinriTag('image', 'b.jpg');
		$this->assertSame($app, $tag->infinri());
	}

	public function testOptionMethodIsRemoved(): void
	{
		$this->assertFalse(method_exists(InfinriTag::class, 'option'));
	}

	public function testParent(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page  = $app->page('a');
		$tag   = new InfinriTag('image', 'b.jpg', [], [
			'parent' => $page,
		]);

		$this->assertSame($page, $tag->parent());
	}

	public static function parseProvider(): array
	{
		return [
			[
				'(test: test value)',
				['some' => 'data'],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'data'    => ['some' => 'data'],
					'attrs'   => []
				]
			],
			[
				'test: test value',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => []
				]
			],
			[
				'test:',
				[],
				[
					'type'    => 'test',
					'value'   => '',
					'attrs'   => []
				]
			],
			[
				'test: ',
				[],
				[
					'type'    => 'test',
					'value'   => '',
					'attrs'   => []
				]
			],
			[
				'test: test value a: attrA b: attrB',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test:test value a:attrA b:attrB',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA b:',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => ''
					]
				]
			],
			[
				'test: test value a: attrA b: ',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => ''
					]
				]
			],
			[
				'test: test value a: attrA b: attrB ',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA c: attrC b: attrB',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA c: attrC',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA b: attrB c: attrC',
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB c: attrC'
					]
				]
			],
			[
				'file: file://abc a: attrA b: attrB c: attrC',
				[],
				[
					'type'    => 'file',
					'value'   => 'file://abc',
					'attrs'   => [
						'a' => 'attrA b: attrB c: attrC'
					]
				]
			],
		];
	}

	#[DataProvider('parseProvider')]
	public function testParse(
		string $string,
		array $data,
		array $expected
	): void {
		$tag = InfinriTag::parse($string, $data);
		foreach ($expected as $key => $value) {
			$this->assertSame($value, $tag->$key);
		}
	}

	public function testRender(): void
	{
		$tag = new InfinriTag('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$this->assertSame('test: test value-attrA-attrB', $tag->render());

		$tag = new InfinriTag('test', '', [
			'a' => 'attrA'
		]);
		$this->assertSame('test: -attrA-', $tag->render());
	}

	public function testRenderNoHtml(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: noHtml');

		$tag = new InfinriTag('noHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	public function testRenderInvalidHtml(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: invalidHtml');

		$tag = new InfinriTag('invalidHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	public function testType(): void
	{
		$tag = new InfinriTag('test', 'test value');
		$this->assertSame('test', $tag->type());
	}
}
