<?php

namespace Infinri\Exception;

class AuthException extends Exception
{
	protected static string $defaultKey = 'auth';
	protected static string $defaultFallback = 'Unauthenticated';
	protected static int $defaultHttpCode = 401;
}
