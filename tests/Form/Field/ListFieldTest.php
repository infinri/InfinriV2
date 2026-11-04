<?php

namespace Infinri\Form\Field;

class ListFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('list');

		$this->assertSame('list', $field->type());
		$this->assertSame('list', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->label());
		$this->assertNull($field->text());
		$this->assertTrue($field->hasValue());
	}

	public function testValueSanitized(): void
	{
		$field = $this->field('list', [
			'value' => '<ul><li>Item <strong>one</strong></li></ul><script>alert("Hacked")</script>'
		]);

		$this->assertSame('<ul><li>Item <strong>one</strong></li></ul>', $field->value());
	}
}
