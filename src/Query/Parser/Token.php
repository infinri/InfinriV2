<?php

namespace Kirby\Query\Parser;

class Token
{
	public function __construct(
		public TokenType $type,
		public string $lexeme,
		public mixed $literal = null,
	) {
	}

	public function is(TokenType $type): bool
	{
		return $this->type === $type;
	}
}
