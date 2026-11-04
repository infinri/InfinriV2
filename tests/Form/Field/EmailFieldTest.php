<?php

namespace Infinri\Form\Field;

class EmailFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('email');

		$this->assertSame('email', $field->type());
		$this->assertSame('email', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('email', $field->icon());
		$this->assertSame('mail@example.com', $field->placeholder());
		$this->assertNull($field->counter());
		$this->assertSame('email', $field->autocomplete());
		$this->assertTrue($field->hasValue());
	}

	public function testEmailValidation(): void
	{
		$field = $this->field('email', [
			'value' => 'mail@infinri.com'
		]);

		$this->assertTrue($field->isValid());

		$field = $this->field('email', [
			'value' => 'mail[at]infinri.com'
		]);

		$this->assertFalse($field->isValid());
	}

	public function testMinLength(): void
	{
		$field = $this->field('email', [
			'value' => 'mail@test.com',
			'minlength' => 14
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength(): void
	{
		$field = $this->field('email', [
			'value'     => 'mail@test.com',
			'maxlength' => 12
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}
}
