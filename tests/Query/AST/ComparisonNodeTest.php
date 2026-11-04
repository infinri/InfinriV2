<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;

class ComparisonNodeTest extends TestCase
{
	public function testResolve()
	{
		$node = new ComparisonNode(
			left: new LiteralNode(5),
			operator: '==',
			right: new LiteralNode(5)
		);

		$visitor = new DefaultVisitor();
		$this->assertTrue($node->resolve($visitor));
	}
}
