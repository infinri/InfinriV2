<?php

namespace Infinri\Form\Mixin;

use Infinri\Cms\Language;

trait Translatable
{
	/**
	 * Should the field be translatable?
	 */
	protected bool $translate = true;

	/**
	 * Should the field be translatable into the given language?
	 */
	public function isTranslatable(Language $language): bool
	{
		if ($this->translate() === false && $language->isDefault() === false) {
			return false;
		}

		return true;
	}

	protected function setTranslate(bool $translate = true): void
	{
		$this->translate = $translate;
	}

	public function translate(): bool
	{
		return $this->translate;
	}
}
