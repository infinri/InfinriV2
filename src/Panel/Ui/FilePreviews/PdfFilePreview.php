<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;

class PdfFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-pdf-file-preview'
	) {
	}

	public static function accepts(File $file): bool
	{
		return $file->extension() === 'pdf';
	}
}
