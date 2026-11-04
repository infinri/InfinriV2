<?php

namespace Infinri\Form\Mixin;

trait Placeholder
{
	/**
	 * Optional placeholder value that will be shown when the field is empty
	 */
	protected array|string|null $placeholder;

	public function placeholder(): string|null
	{
		return $this->stringTemplate(
			$this->placeholder
		);
	}

	protected function setPlaceholder(array|string|null $placeholder = null): void
	{
		$this->placeholder = $this->i18n($placeholder);
	}
}
