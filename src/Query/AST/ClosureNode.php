<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

class ClosureNode extends Node
{
	/**
	 * @param string[] $arguments The arguments names
	 */
	public function __construct(
		public array $arguments,
		public Node $body,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->closure($this);
	}
}
