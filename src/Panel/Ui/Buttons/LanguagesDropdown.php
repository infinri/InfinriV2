<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\Cms\App;
use Infinri\Cms\Language;
use Infinri\Cms\Languages;
use Infinri\Cms\ModelWithContent;
use Infinri\Toolkit\Str;

class LanguagesDropdown extends ViewButton
{
	protected App $infinri;

	public function __construct(
		ModelWithContent $model
	) {
		$this->infinri = $model->infinri();

		parent::__construct(
			component: 'k-languages-dropdown',
			model: $model,
			class: 'k-languages-dropdown',
			icon: 'translate',
			// Fiber dropdown endpoint to load options
			// only when dropdown is opened
			options: $model->panel()->url(true) . '/languages',
			responsive: 'text',
			text: Str::upper($this->infinri->language()?->code())
		);
	}

	/**
	 * Returns if any translation other than the current one has unsaved changes
	 * (the current language has to be handled in `k-languages-dropdown` as its
	 * state can change dynamically without another backend request)
	 */
	public function hasDiff(): bool
	{
		foreach (Languages::ensure() as $language) {
			if ($this->infinri->language()?->code() !== $language->code()) {
				if ($this->model->version('changes')->exists($language) === true) {
					return true;
				}
			}
		}

		return false;
	}

	public function option(Language $language): array
	{
		$changes = $this->model->version('changes');

		return [
			'text'    => $language->name(),
			'code'    => $language->code(),
			'current' => $language->code() === $this->infinri->language()?->code(),
			'default' => $language->isDefault(),
			'changes' => $changes->exists($language),
			'lock'    => $changes->isLocked('*')
		];
	}

	/**
	 * Options are used in the Fiber dropdown routes
	 */
	public function options(): array
	{
		$languages = $this->infinri->languages();
		$options   = [];

		if ($this->infinri->multilang() === false) {
			return $options;
		}

		// add the primary/default language first
		if ($default = $languages->default()) {
			$options[] = $this->option($default);
			$options[] = '-';
			$languages = $languages->not($default);
		}

		// add all secondary languages after the separator
		foreach ($languages as $language) {
			$options[] = $this->option($language);
		}

		return $options;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'hasDiff' => $this->hasDiff()
		];
	}

	public function render(): array|null
	{
		// hides the language selector when there are less than 2 languages
		if ($this->infinri->languages()->count() < 2) {
			return null;
		}

		return parent::render();
	}
}
