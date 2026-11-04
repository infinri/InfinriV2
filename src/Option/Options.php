<?php

namespace Infinri\Option;

use Infinri\Cms\Collection;
use Infinri\Cms\ModelWithContent;
use Infinri\Toolkit\A;

class Options extends Collection
{
	public function __construct(array $objects = [])
	{
		foreach ($objects as $object) {
			$this->__set($object->value, $object);
		}
	}

	/**
	 * The Infinri Collection class only shows the key to
	 * avoid huge trees when dumping, but for the options
	 * collections this is really not useful
	 *
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return A::map($this->data, fn ($item) => (array)$item);
	}

	/**
	 * @param bool $resolve Whether query templates in the text and info get resolved
	 */
	public static function factory(
		array $items = [],
		bool $resolve = true
	): static {
		$collection = new static();

		foreach ($items as $key => $option) {
			// convert an associative value => text array into props;
			// skip if option is already an array of option props
			if (
				is_array($option) === false ||
				array_key_exists('value', $option) === false
			) {
				$option = match (true) {
					is_string($key) => ['value' => $key, 'text' => $option],
					default         => ['value' => $option]
				};
			}

			$option = Option::factory($option, $resolve);
			$collection->__set($option->id(), $option);
		}

		return $collection;
	}

	public function render(ModelWithContent $model, bool $safeMode = true): array
	{
		$options = [];

		foreach ($this->data as $key => $option) {
			$options[$key] = $option->render($model, $safeMode);
		}

		return array_values($options);
	}
}
