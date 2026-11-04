<?php

namespace Infinri\Cms;

use Infinri\Exception\DuplicateException;
use Infinri\Exception\InvalidArgumentException;

class LanguageVariable
{
	protected App $infinri;

	public function __construct(
		protected Language $language,
		protected string $key
	) {
		$this->infinri = App::instance();
	}

	/**
	 * Creates a new language variable. This will
	 * be added to the default language first and
	 * can then be translated in other languages.
	 */
	public static function create(
		string $key,
		string|array|null $value = null
	): static {
		if (is_numeric($key) === true) {
			throw new InvalidArgumentException(
				key: 'language.variable.numeric'
			);
		}

		if ($key === '') {
			throw new InvalidArgumentException(
				key: 'language.variable.key'
			);
		}

		$infinri        = App::instance();
		$language     = $infinri->defaultLanguage();
		$translations = $language->translations();

		if (isset($translations[$key]) === true) {
			throw new DuplicateException(
				key: 'language.variable.duplicate',
				data: ['key' => $key]
			);
		}

		if (isset($infinri->coreI18nStrings($language->code())[$key]) === true) {
			throw new DuplicateException(
				key: 'language.variable.core',
				data: ['key' => $key]
			);
		}

		$translations[$key] = $value ?? '';

		$language = $language->update(['translations' => $translations]);

		return $language->variable($key);
	}

	/**
	 * Deletes a language variable from the translations array.
	 * This will go through all language files and delete the
	 * key from all translation arrays to keep them clean.
	 */
	public function delete(): bool
	{
		// go through all languages and remove the variable
		foreach ($this->infinri->languages() as $language) {
			$variables = $language->translations();

			unset($variables[$this->key]);

			$language->update(['translations' => $variables]);
		}

		return true;
	}

	/**
	 * Checks if a language variable exists in the default language
	 */
	public function exists(): bool
	{
		$language = $this->infinri->defaultLanguage();
		return isset($language->translations()[$this->key]) === true;
	}

	/**
	 * Checks if the value is an array
	 */
	public function hasMultipleValues(): bool
	{
		return is_array($this->value()) === true;
	}

	/**
	 * Returns the unique key for the variable
	 */
	public function key(): string
	{
		return $this->key;
	}

	/**
	 * Returns the parent language
	 */
	public function language(): Language
	{
		return $this->language;
	}

	/**
	 * Sets a new value for the language variable
	 */
	public function update(string|array|null $value = null): static
	{
		$translations             = $this->language->translations();
		$translations[$this->key] = $value ?? '';

		$language = $this->language->update(['translations' => $translations]);

		return $language->variable($this->key);
	}

	/**
	 * Returns the value if the variable has been translated.
	 */
	public function value(): string|array|null
	{
		return $this->language->translations()[$this->key] ?? null;
	}
}
