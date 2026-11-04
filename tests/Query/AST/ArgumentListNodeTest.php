<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArgumentListNode::class)]
class ArgumentListNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new ArgumentListNode([
			new LiteralNode('a'),
			new LiteralNode(7)
		]);

		$visitor = new DefaultVisitor();
		$this->assertSame(['a', 7], $node->resolve($visitor));
	}
}
