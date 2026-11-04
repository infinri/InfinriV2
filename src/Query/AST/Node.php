<?php

namespace Infinri\Query\AST;

use Infinri\Query\Visitors\Visitor;

abstract class Node
{
	abstract public function resolve(Visitor $visitor);
}
