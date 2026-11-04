<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;

class DefaultFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-default-file-preview'
	) {
	}

	/**
	 * Accepts any file as last resort
	 */
	public static function accepts(File $file): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'image' => $this->image()
		];
	}
}
