<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;

class LogicalNodeTest extends TestCase
{
	public function testResolveAnd()
	{
		$node = new LogicalNode(
			left: new LiteralNode(true),
			operator: '&&',
			right: new LiteralNode(false)
		);

		$visitor = new DefaultVisitor();
		$this->assertFalse($node->resolve($visitor));
	}

	public function testResolveOr()
	{
		$node = new LogicalNode(
			left: new LiteralNode(false),
			operator: '||',
			right: new LiteralNode(true)
		);

		$visitor = new DefaultVisitor();
		$this->assertTrue($node->resolve($visitor));
	}
}
