<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

class TernaryNode extends Node
{
	public function __construct(
		public Node $condition,
		public Node $false,
		public Node|null $true = null
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->ternary(
			condition: $this->condition->resolve($visitor),
			true:      $this->true?->resolve($visitor),
			false:     $this->false->resolve($visitor)
		);
	}
}
