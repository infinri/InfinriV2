<?php

namespace Kirby\Template;

use Countable;

class Slots implements Countable
{
	/**
	 * Creates a new slots collection
	 */
	public function __construct(
		protected array $slots
	) {
	}

	/**
	 * Magic getter for slots;
	 * e.g. `$slots->heading`
	 */
	public function __get(string $name): Slot|null
	{
		return $this->slots[$name] ?? null;
	}

	/**
	 * Magic getter method for slots;
	 * e.g. `$slots->heading()`
	 */
	public function __call(string $name, array $args): Slot|null
	{
		return $this->__get($name);
	}

	/**
	 * Counts the number of defined slots
	 */
	public function count(): int
	{
		return count($this->slots);
	}
}
