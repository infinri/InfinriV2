<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CoalesceNode::class)]
class CoalesceNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new CoalesceNode(
			left: new LiteralNode(null),
			right: new LiteralNode('foo')
		);

		$visitor = new DefaultVisitor();
		$this->assertSame('foo', $node->resolve($visitor));
	}
}
