<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClosureNode::class)]
class ClosureNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new ClosureNode(
			arguments: ['a', 'b'],
			body: new CoalesceNode(
				left: new VariableNode('a'),
				right: new VariableNode('b')
			)
		);

		$visitor = new DefaultVisitor();
		$this->assertEquals(fn ($a, $b) => $a ?? $b, $node->resolve($visitor));
	}
}
