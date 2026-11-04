<?php

namespace Infinri\Panel\Ui\FilePreviews;

use Infinri\Cms\File;
use Infinri\Panel\Ui\FilePreview;

class AudioFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-audio-file-preview'
	) {
	}

	public static function accepts(File $file): bool
	{
		return $file->type() === 'audio';
	}
}
