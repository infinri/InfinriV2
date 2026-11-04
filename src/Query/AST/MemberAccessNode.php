<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

class MemberAccessNode extends Node
{
	public function __construct(
		public Node $object,
		public Node $member,
		public ArgumentListNode|null $arguments = null,
		public bool $nullSafe = false,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->memberAccess(
			object:    $this->object->resolve($visitor),
			member:    $this->member->resolve($visitor),
			arguments: $this->arguments?->resolve($visitor),
			nullSafe:  $this->nullSafe
		);
	}
}
