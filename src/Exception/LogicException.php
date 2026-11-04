<?php

namespace Kirby\Exception;

class LogicException extends Exception
{
	protected static string $defaultKey = 'logic';
	protected static string $defaultFallback = 'This task cannot be finished';
	protected static int $defaultHttpCode = 400;
}
