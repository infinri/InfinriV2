<?php

namespace Infinri\Form\Mixin;

trait Help
{
	/**
	 * Optional help text below the field
	 */
	protected string|null $help;

	public function help(): string|null
	{
		if (empty($this->help) === false) {
			$help = $this->stringTemplate($this->help);
			$help = $this->kirby()->kirbytext($help);
			return $help;
		}

		return null;
	}

	protected function setHelp(array|string|null $help = null): void
	{
		$this->help = $this->i18n($help);
	}
}
