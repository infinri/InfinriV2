<?php

namespace Infinri\Exception;

class DuplicateException extends Exception
{
	protected static string $defaultKey = 'duplicate';
	protected static string $defaultFallback = 'The entry exists';
	protected static int $defaultHttpCode = 400;
}
