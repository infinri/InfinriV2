<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

abstract class Node
{
	abstract public function resolve(Visitor $visitor);
}
