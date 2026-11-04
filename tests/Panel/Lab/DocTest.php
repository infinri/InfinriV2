<?php

namespace Infinri\Panel\Lab;

use Infinri\Cms\App;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Doc::class)]
class DocTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::FIXTURES,
				'panel' => static::FIXTURES . '/panel'
			]
		]);
	}

	protected function tearDown(): void
	{
		App::destroy();
	}

	public function testFactoryReadsFixture(): void
	{
		$this->assertFileExists(Doc::file('k-sample', 'dev'));
		$doc = Doc::factory('k-sample');

		$this->assertInstanceOf(Doc::class, $doc);
		$this->assertSame('k-sample', $doc->name);
		$this->assertCount(1, $doc->props);
		$this->assertSame('label', $doc->props[0]['name']);
		$this->assertSame('focus', $doc->methods[0]['name']);
		$this->assertSame('input', $doc->events[0]['name']);
		$this->assertSame('default', $doc->slots[0]['name']);
	}

	public function testSourceIsRemoved(): void
	{
		$this->assertFalse(method_exists(Doc::class, 'source'));
	}

	public function testToArrayHasNoSourceSinceOrDeprecated(): void
	{
		$data = Doc::factory('k-sample')->toArray();

		$this->assertArrayNotHasKey('source', $data);
		$this->assertArrayNotHasKey('since', $data);
		$this->assertArrayNotHasKey('deprecated', $data);
	}

	public function testPartsHaveNoSinceOrDeprecated(): void
	{
		$data = Doc::factory('k-sample')->toArray();

		foreach (['props', 'methods', 'events', 'slots'] as $part) {
			$this->assertArrayNotHasKey('since', $data[$part][0], $part);
			$this->assertArrayNotHasKey('deprecated', $data[$part][0], $part);
		}
	}

	public function testDocHasNoSinceOrDeprecatedProperties(): void
	{
		foreach ([Doc::class, Doc\Prop::class, Doc\Method::class, Doc\Event::class, Doc\Slot::class] as $class) {
			$reflection = new \ReflectionClass($class);

			$this->assertFalse($reflection->hasProperty('since'), $class . '::$since');
			$this->assertFalse($reflection->hasProperty('deprecated'), $class . '::$deprecated');
		}
	}
}
