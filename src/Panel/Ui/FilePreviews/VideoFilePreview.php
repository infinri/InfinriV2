<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;

class VideoFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-video-file-preview'
	) {
	}

	public static function accepts(File $file): bool
	{
		return $file->type() === 'video';
	}
}
