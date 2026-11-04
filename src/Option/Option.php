<?php

namespace Infinri\Option;

use Infinri\Cms\ModelWithContent;
use Infinri\Toolkit\I18n;

class Option
{
	public string|array $text;

	/**
	 * @param bool $resolve Whether query templates in the text and info get resolved
	 */
	public function __construct(
		public string|int|float|null $value,
		public bool $disabled = false,
		public string|null $icon = null,
		public string|array|null $info = null,
		string|array|null $text = null,
		public bool $resolve = true
	) {
		$this->text = $text ?? ['en' => $this->value];
	}

	/**
	 * @param bool $resolve Whether query templates in the text and info get resolved
	 */
	public static function factory(
		string|int|float|array|null $props,
		bool $resolve = true
	): static {
		if (is_array($props) === false) {
			$props = ['value' => $props];
		}

		// Normalize info to be an array
		if (isset($props['info']) === true) {
			$props['info'] = match (true) {
				is_array($props['info']) => $props['info'],
				$props['info'] === null,
				$props['info'] === false => null,
				default                  => ['en' => $props['info']]
			};
		}

		// Normalize text to be an array
		if (isset($props['text']) === true) {
			$props['text'] = match (true) {
				is_array($props['text']) => $props['text'],
				$props['text'] === null,
				$props['text'] === false => null,
				default                  => ['en' => $props['text']]
			};
		}

		return new static(...$props, resolve: $resolve);
	}

	public function id(): string|int|float
	{
		return $this->value ?? '';
	}

	/**
	 * Renders all data for the option
	 */
	public function render(
		ModelWithContent $model,
		bool $safeMode = true
	): array {
		$info = I18n::translate($this->info, $this->info);
		$text = I18n::translate($this->text, $this->text);
		$method = $safeMode === true ? 'toSafeString' : 'toString';

		return [
			'disabled' => $this->disabled,
			'icon'     => $this->icon,
			'info'     => $info && $this->resolve === true ? $model->$method($info) : $info,
			'text'     => $text && $this->resolve === true ? $model->$method($text) : $text,
			'value'    => $this->value
		];
	}
}
