<?php

namespace Infinri\Http\Request;

class Files
{
	use Data;

	/**
	 * Sanitized array of all received files
	 */
	protected array $files = [];

	/**
	 * Creates a new Files object
	 * Pass your own array to mock
	 * uploads.
	 */
	public function __construct(array|null $files = null)
	{
		$files ??= $_FILES;

		foreach ($files as $key => $file) {
			if (is_array($file['name']) === true) {
				foreach ($file['name'] as $i => $name) {
					$this->files[$key][] = [
						'name'     => $file['name'][$i]      ?? null,
						'type'     => $file['type'][$i]      ?? null,
						'tmp_name' => $file['tmp_name'][$i]  ?? null,
						'error'    => $file['error'][$i]     ?? null,
						'size'     => $file['size'][$i]      ?? null,
					];
				}
			} else {
				$this->files[$key] = $file;
			}
		}
	}

	/**
	 * The data method returns the files
	 * array. This is only needed to make
	 * the Data trait work for the Files::get($key)
	 * method.
	 */
	public function data(): array
	{
		return $this->files;
	}
}
