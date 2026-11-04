<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PreviewButton::class)]
class PreviewButtonTest extends TestCase
{
	public function testButton(): void
	{
		$button = new PreviewButton(link: 'https://infinri.com');

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-preview-view-button', $button->class);
		$this->assertSame('window', $button->icon);
		$this->assertSame('https://infinri.com', $button->link);
		$this->assertSame(null, $button->target);
		$this->assertSame('Preview', $button->title);
	}

}
