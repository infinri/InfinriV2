<?php

namespace Infinri\Cms;

use Infinri\Content\Content;

class StructureObject extends Item
{
	use HasMethods;

	public const ITEMS_CLASS = Structure::class;

	protected Content $content;

	/**
	 * Creates a new StructureObject with the given props
	 */
	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->content = new Content(
			$params['content'] ?? $params['params'] ?? [],
			$this->parent
		);
	}

	/**
	 * Modified getter to also return fields
	 * from the object's content
	 */
	public function __call(string $method, array $args = []): mixed
	{
		// structure object methods
		if ($this->hasMethod($method) === true) {
			return $this->callMethod($method, $args);
		}

		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		return $this->content()->get($method);
	}

	/**
	 * Returns the content
	 */
	public function content(): Content
	{
		return $this->content;
	}

	/**
	 * Converts all fields in the object to a
	 * plain associative array. The id is
	 * injected from the parent into the array
	 * to make sure it's always present and
	 * not overloaded by the content.
	 */
	public function toArray(): array
	{
		return [
			...$this->content()->toArray(),
			...parent::toArray()
		];
	}
}
