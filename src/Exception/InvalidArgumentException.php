<?php

namespace Infinri\Exception;

class InvalidArgumentException extends Exception
{
	protected static string $defaultKey = 'invalidArgument';
	protected static string $defaultFallback = 'Invalid argument "{ argument }" in method "{ method }"';
	protected static int $defaultHttpCode = 400;
	protected static array $defaultData = ['argument' => null, 'method' => null];
}
