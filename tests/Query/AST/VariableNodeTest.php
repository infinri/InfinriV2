<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\DefaultVisitor;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VariableNode::class)]
class VariableNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new VariableNode('a');

		$visitor = new DefaultVisitor(context: ['a' => 'foo']);
		$this->assertSame('foo', $node->resolve($visitor));
	}
}
