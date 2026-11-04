<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class CoalesceNode extends Node
{
	public function __construct(
		public Node $left,
		public Node $right,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->coalescence(
			left:  $this->left->resolve($visitor),
			right: $this->right->resolve($visitor)
		);
	}
}
