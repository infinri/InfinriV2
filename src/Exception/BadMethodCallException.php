<?php

namespace Infinri\Exception;

class BadMethodCallException extends Exception
{
	protected static string $defaultKey = 'invalidMethod';
	protected static string $defaultFallback = 'The method "{ method }" does not exist';
	protected static int $defaultHttpCode = 400;
	protected static array $defaultData = ['method' => null];
}
