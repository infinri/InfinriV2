<?php

namespace Infinri\Panel\Lab;

use Infinri\Template\Template as BaseTemplate;

class Template extends BaseTemplate
{
	public function __construct(
		public string $file
	) {
		parent::__construct(
			name: basename($this->file)
		);
	}

	public function file(): string|null
	{
		return $this->file;
	}
}
