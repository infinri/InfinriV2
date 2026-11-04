<?php

namespace Infinri\Http;

use Infinri\Toolkit\Collection;
use Infinri\Toolkit\Str;

class Path extends Collection
{
	public function __construct(string|array|null $items)
	{
		if (is_string($items) === true) {
			$items = Str::split($items, '/');
		}

		parent::__construct($items ?? []);
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function toString(
		bool $leadingSlash = false,
		bool $trailingSlash = false
	): string {
		if ($this->data === []) {
			return '';
		}

		$path = implode('/', $this->data);

		$leadingSlash  = $leadingSlash  === true ? '/' : null;
		$trailingSlash = $trailingSlash === true ? '/' : null;

		return $leadingSlash . $path . $trailingSlash;
	}
}
