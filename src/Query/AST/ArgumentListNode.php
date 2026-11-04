<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class ArgumentListNode extends Node
{
	public function __construct(
		public array $arguments = []
	) {
	}

	public function resolve(Visitor $visitor): array|string
	{
		// Resolve each argument
		$arguments = array_map(
			fn ($argument) => $argument->resolve($visitor),
			$this->arguments
		);

		// Keep as array or convert to string
		// depending on the visitor type
		return $visitor->arguments($arguments);
	}
}
