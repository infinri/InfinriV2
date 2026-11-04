<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TernaryNode::class)]
class TernaryNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new TernaryNode(
			condition: new LiteralNode(false),
			false: new LiteralNode(5.0)
		);

		$visitor = new DefaultVisitor();
		$this->assertSame(5.0, $node->resolve($visitor));
	}
}
