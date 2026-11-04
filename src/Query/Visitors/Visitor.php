<?php

namespace Kirby\Query\Visitors;

use Closure;

abstract class Visitor
{
	/**
	 * @param array<string,Closure> $global valid global function closures
	 * @param array<string,mixed> $context data bindings for the query
	 */
	public function __construct(
		public array $global = [],
		public array $context = [],
		protected Closure|null $interceptor = null
	) {
	}
}
