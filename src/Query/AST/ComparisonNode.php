<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class ComparisonNode extends Node
{
	public function __construct(
		public Node $left,
		public string $operator,
		public Node $right
	) {
	}

	public function resolve(Visitor $visitor): bool|string
	{
		return $visitor->comparison(
			left: $this->left->resolve($visitor),
			operator: $this->operator,
			right: $this->right->resolve($visitor)
		);
	}
}
