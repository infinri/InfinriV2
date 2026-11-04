<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class LiteralNode extends Node
{
	public function __construct(
		public mixed $value,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->literal($this->value);
	}
}
