<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GlobalFunctionNode::class)]
class GlobalFunctionNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new GlobalFunctionNode(
			name: 'foo',
			arguments: new ArgumentListNode([
				new LiteralNode(3),
				new LiteralNode(7)
			])
		);

		$functions = ['foo' => fn ($a, $b) => $a + $b];

		$visitor = new DefaultVisitor(global: $functions);
		$this->assertSame(10, $node->resolve($visitor));
	}
}
