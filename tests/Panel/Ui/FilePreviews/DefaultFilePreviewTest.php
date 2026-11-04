<?php

namespace Infinri\Panel\Ui\FilePreviews;

use Infinri\Cms\File;
use Infinri\Cms\Page;
use Infinri\Panel\Ui\FilePreview;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DefaultFilePreview::class)]
class DefaultFilePreviewTest extends TestCase
{
	public function testAccepts(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertTrue(DefaultFilePreview::accepts($file));
	}

	public function testFactory(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.zip', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(DefaultFilePreview::class, $preview);
		$this->assertSame('k-default-file-preview', $preview->component);
	}

	public function testProps(): void
	{
		$page      = new Page(['slug' => 'test']);
		$file      = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$component = new DefaultFilePreview($file);
		$props     = $component->props();

		$this->assertIsArray($props['image']);
	}
}
