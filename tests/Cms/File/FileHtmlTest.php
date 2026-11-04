<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileHtmlTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.FileHtml';

	public function testHtml(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'url'      => 'http://infinri.com/test.jpg',
			'parent'   => $this->app->site(),
			'content' => [
				'alt' => 'This is the alt text'
			]
		]);

		$this->assertSame(
			'<img alt="This is the alt text" src="http://infinri.com/test.jpg">',
			$file->html()
		);
	}
}
