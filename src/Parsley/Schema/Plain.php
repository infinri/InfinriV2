<?php

namespace Infinri\Parsley\Schema;

use Infinri\Parsley\Element;
use Infinri\Parsley\Schema;
use Infinri\Toolkit\Str;

class Plain extends Schema
{
	/**
	 * Creates the fallback block type
	 * if no other block can be found
	 */
	public function fallback(Element|string $element): array|null
	{
		if ($element instanceof Element) {
			$text = $element->innerText();
		} elseif (is_string($element) === true) {
			$text = trim($element);

			if (Str::length($text) === 0) {
				return null;
			}
		} else {
			return null;
		}

		return [
			'content' => [
				'text' => $text
			],
			'type' => 'text',
		];
	}

	/**
	 * Returns a list of all elements that
	 * should be skipped during parsing
	 */
	public function skip(): array
	{
		return [
			'base',
			'link',
			'meta',
			'script',
			'style',
			'title'
		];
	}
}
