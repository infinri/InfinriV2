<?php

namespace Infinri\Cms;

use Infinri\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileUnpublishTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.FileUnpublish';

	public function testUnpublish(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);

		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileDoesNotExist($file->mediaRoot());
		$file->publish();
		$this->assertFileExists($file->mediaRoot());
		$file->unpublish();
		$this->assertFileDoesNotExist($file->mediaRoot());
	}
}
