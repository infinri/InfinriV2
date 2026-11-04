<?php

namespace Infinri\Form\Mixin;

trait Icon
{
	/**
	 * Optional icon that will be shown at the end of the field
	 */
	protected string|null $icon;

	public function icon(): string|null
	{
		return $this->icon;
	}

	protected function setIcon(string|null $icon = null): void
	{
		$this->icon = $icon;
	}
}
