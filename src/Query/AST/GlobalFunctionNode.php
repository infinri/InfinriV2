<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

class GlobalFunctionNode extends Node
{
	public function __construct(
		public string $name,
		public ArgumentListNode $arguments,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->function(
			name:      $this->name,
			arguments: $this->arguments->resolve($visitor)
		);
	}
}
