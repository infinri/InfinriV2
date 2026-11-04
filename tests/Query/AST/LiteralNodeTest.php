<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LiteralNode::class)]
class LiteralNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new LiteralNode('a');

		$visitor = new DefaultVisitor();
		$this->assertSame('a', $node->resolve($visitor));
	}
}
