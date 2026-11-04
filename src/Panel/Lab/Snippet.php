<?php

namespace Infinri\Panel\Lab;

use Infinri\Template\Snippet as BaseSnippet;

class Snippet extends BaseSnippet
{
	public static function root(): string
	{
		return __DIR__ . '/snippets';
	}
}
