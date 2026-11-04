<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class ArrayListNode extends Node
{
	public function __construct(
		public array $elements,
	) {
	}

	public function resolve(Visitor $visitor): array|string
	{
		// Resolve each array element
		$elements = array_map(
			fn ($element) => $element->resolve($visitor),
			$this->elements
		);

		// Keep as array or convert to string
		// depending on the visitor type
		return $visitor->arrayList($elements);
	}
}
