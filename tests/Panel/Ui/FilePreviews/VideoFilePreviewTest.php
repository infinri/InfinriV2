<?php

namespace Infinri\Panel\Ui\FilePreviews;

use Infinri\Cms\File;
use Infinri\Cms\Page;
use Infinri\Panel\Ui\FilePreview;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VideoFilePreview::class)]
class VideoFilePreviewTest extends TestCase
{
	public function testAccepts(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.mp4', 'parent' => $page]);
		$this->assertTrue(VideoFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(VideoFilePreview::accepts($file));
	}

	public function testFactory(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.mp4', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(VideoFilePreview::class, $preview);
		$this->assertSame('k-video-file-preview', $preview->component);
	}
}
