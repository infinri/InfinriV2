<?php

namespace Infinri\Cms;

use Infinri\Exception\DuplicateException;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Filesystem\Dir;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageVariable::class)]
class LanguageVariableTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.LanguageVariable';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				]
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testCreate(): void
	{
		// impersonate infinri to make sure the translation
		// is created in the default language
		$this->app->impersonate('infinri');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertSame('bar', $variable->value());
	}

	public function testCreateWithMultipleValues(): void
	{
		// impersonate infinri to make sure the translation
		// is created in the default language
		$this->app->impersonate('infinri');

		$variable = LanguageVariable::create('foo', ['bar', 'baz']);
		$this->assertSame(['bar', 'baz'], $variable->value());
	}

	public function testCreateEmptyKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid key for the variable');

		LanguageVariable::create('');
	}

	public function testCreateInvalidKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable key must not be numeric');

		LanguageVariable::create('0');
	}

	public function testCreateWithDuplicateKey(): void
	{
		$this->app->impersonate('infinri');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A variable for "foo" already exists');

		LanguageVariable::create('foo', 'bar');
		LanguageVariable::create('foo', 'baz');
	}

	public function testCreateWithCoreKey(): void
	{
		$this->app->impersonate('infinri');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('"date" is one of Infinri\'s own translation strings and cannot be overwritten by a language variable');

		LanguageVariable::create('date', 'bar');
	}

	public function testDelete(): void
	{
		$this->app->impersonate('infinri');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertTrue($variable->exists());
		$variable->delete();
		$this->assertFalse($variable->exists());
	}

	public function testExists(): void
	{
		$this->app->impersonate('infinri');

		$variable = new LanguageVariable($this->app->defaultLanguage(), 'foo');
		$this->assertFalse($variable->exists());

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertTrue($variable->exists());
	}

	public function testHasMultipleValues(): void
	{
		// impersonate infinri to make sure the translation
		// is created in the default language
		$this->app->impersonate('infinri');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertFalse($variable->hasMultipleValues());

		$variable = LanguageVariable::create('foz', ['bar', 'baz']);
		$this->assertTrue($variable->hasMultipleValues());
	}

	public function testKey(): void
	{
		$language = new Language(['code' => 'test']);
		$variable = new LanguageVariable($language, 'foo');
		$this->assertSame('foo', $variable->key());
	}

	public function testLanguage(): void
	{
		$language = new Language(['code' => 'test']);
		$variable = new LanguageVariable($language, 'foo');
		$this->assertSame($language, $variable->language());
	}

	public function testUpdate(): void
	{
		// impersonate infinri to allow updating the variable
		$this->app->impersonate('infinri');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertSame('bar', $variable->value());

		$variable = $variable->update('baz');
		$this->assertSame('baz', $variable->value());
	}
}
