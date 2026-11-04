<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

class LogicalNode extends Node
{
	public function __construct(
		public Node $left,
		public string $operator,
		public Node $right
	) {
	}

	public function resolve(Visitor $visitor): bool|string
	{
		return $visitor->logical(
			left: $this->left->resolve($visitor),
			operator: $this->operator,
			right: $this->right->resolve($visitor)
		);
	}
}
