<?php

namespace Infinri\Cms;

use Infinri\Data\Json;
use Infinri\Parsley\Parsley;
use Infinri\Parsley\Schema\Blocks as BlockSchema;
use Infinri\Toolkit\A;
use Infinri\Toolkit\Str;
use Throwable;

class Blocks extends Items
{
	public const ITEM_CLASS = Block::class;

	/**
	 * All registered blocks methods
	 */
	public static array $methods = [];

	/**
	 * Return HTML when the collection is
	 * converted to a string
	 */
	public function __toString(): string
	{
		return $this->toHtml();
	}

	/**
	 * Converts the blocks to HTML and then
	 * uses the Str::excerpt method to create
	 * a non-formatted, shortened excerpt from it
	 */
	public function excerpt(mixed ...$args): string
	{
		return Str::excerpt($this->toHtml(), ...$args);
	}

	/**
	 * Wrapper around the factory to
	 * catch blocks from layouts
	 */
	public static function factory(
		array|null $items = null,
		array $params = []
	): static {
		$items = static::extractFromLayouts($items);

		return parent::factory($items, $params);
	}

	/**
	 * Pull out blocks from layouts
	 */
	protected static function extractFromLayouts(array $input): array
	{
		if ($input === []) {
			return [];
		}

		// no columns = no layout
		if (array_key_exists('columns', $input[0]) === false) {
			return $input;
		}

		$blocks = [];

		foreach ($input as $layout) {
			foreach (($layout['columns'] ?? []) as $column) {
				foreach (($column['blocks'] ?? []) as $block) {
					$blocks[] = $block;
				}
			}
		}

		return $blocks;
	}

	/**
	 * Checks if a given block type exists in the collection
	 */
	public function hasType(string $type): bool
	{
		return $this->filterBy('type', $type)->count() > 0;
	}

	/**
	 * Parse and sanitize various block formats
	 */
	public static function parse(array|string|null $input): array
	{
		if (
			empty($input) === false &&
			is_array($input) === false
		) {
			try {
				$input = Json::decode((string)$input);
			} catch (Throwable) {
				$parser = new Parsley((string)$input, new BlockSchema());
				$input  = $parser->blocks();
			}
		}

		if (empty($input) === true) {
			return [];
		}

		return $input;
	}

	/**
	 * Convert all blocks to HTML
	 */
	public function toHtml(): string
	{
		$html = A::map($this->data, fn ($block) => $block->toHtml());

		return implode($html);
	}
}
