<?php

namespace Infinri\Cms;

use Closure;

class Ingredients
{
	/**
	 * Creates a new ingredient collection
	 */
	public function __construct(
		protected array $ingredients = []
	) {
	}

	/**
	 * Magic getter for single ingredients
	 */
	public function __call(string $method, array|null $args = null): mixed
	{
		return $this->ingredients[$method] ?? null;
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->ingredients;
	}

	/**
	 * Get a single ingredient by key
	 */
	public function __get(string $key)
	{
		return $this->ingredients[$key] ?? null;
	}

	/**
	 * Resolves all ingredient callbacks
	 * and creates a plain array
	 * @internal
	 */
	public static function bake(array $ingredients): static
	{
		foreach ($ingredients as $name => $ingredient) {
			if ($ingredient instanceof Closure) {
				$ingredients[$name] = $ingredient($ingredients);
			}
		}

		return new static($ingredients);
	}

	/**
	 * Returns all ingredients as plain array
	 */
	public function toArray(): array
	{
		return $this->ingredients;
	}
}
