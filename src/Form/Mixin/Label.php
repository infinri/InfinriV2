<?php

namespace Infinri\Form\Mixin;

use Infinri\Toolkit\Str;

trait Label
{
	/**
	 * The field label can be set as string or associative array with translations
	 */
	protected string|null $label;

	public function label(): string|null
	{
		return $this->stringTemplate(
			$this->label ?? Str::ucfirst($this->name())
		);
	}

	protected function setLabel(array|string|null $label = null): void
	{
		$this->label = $this->i18n($label);
	}
}
