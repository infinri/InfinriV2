<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class VariableNode extends Node
{
	public function __construct(
		public string $name,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->variable($this->name);
	}
}
