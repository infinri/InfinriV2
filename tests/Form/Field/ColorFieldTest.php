<?php

namespace Infinri\Form\Field;

use Infinri\Cms\App;
use Infinri\Exception\InvalidArgumentException;

class ColorFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('color');

		$this->assertSame('color', $field->type());
		$this->assertSame('color', $field->name());
		$this->assertNull($field->value());
		$this->assertFalse($field->alpha());
		$this->assertSame('hex', $field->format());
		$this->assertSame('picker', $field->mode());
		$this->assertTrue($field->hasValue());
	}

	public function testEmptyColor(): void
	{
		$field = $this->field('color', [
			'value' => null
		]);

		$this->assertNull($field->value());
		$this->assertNull($field->toString());
	}

	public function testDefault(): void
	{
		$field = $this->field('color', [
			'default' => '#fff',
		]);

		$this->assertSame('#fff', $field->default());
	}

	public function testFillWithEmptyValue(): void
	{
		$field = $this->field('color');
		$field->fill('#efefef');

		$this->assertSame('#efefef', $field->toFormValue());

		$field->fillWithEmptyValue();

		$this->assertSame('', $field->toFormValue());
	}

	public function testFormatInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->field('color', ['format' => 'foo']);
	}

	public function testModeInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->field('color', ['mode' => 'foo']);
	}

	public function testOptions(): void
	{
		// Only values
		$field = $this->field('color', [
			'options' => ['#aaa', '#bbb', '#ccc'],
		]);

		$this->assertSame([
			['value' => '#aaa'],
			['value' => '#bbb'],
			['value' => '#ccc']
		], $field->options());

		// Value => Name
		$field = $this->field('color', [
			'options' => [
				'#aaa' => 'Color a',
				'#bbb' => 'Color b',
				'#ccc' => 'Color c'
			],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());
	}

	public function testOptionsAreNotFlippedForTextValueNotation(): void
	{
		$field = $this->field('color', [
			'options' => [
				'Color a' => '#aaa',
				'Color b' => '#bbb'
			],
		]);

		$this->assertSame([
			['value' => 'Color a', 'text' => '#aaa'],
			['value' => 'Color b', 'text' => '#bbb']
		], $field->options());
	}

	public function testOptionsFromQuery(): void
	{
		// Only values
		$this->app = new App([
			'options' => [
				'foo' => ['#aaa', '#bbb', '#ccc']
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'infinri.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa'],
			['value' => '#bbb'],
			['value' => '#ccc']
		], $field->options());

		// Value => Name
		$this->app = new App([
			'options' => [
				'foo' => [
					'#aaa' => 'Color a',
					'#bbb' => 'Color b',
					'#ccc' => 'Color c'
				]
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'infinri.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());
	}
}
