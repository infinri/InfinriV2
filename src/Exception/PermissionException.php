<?php

namespace Infinri\Exception;

class PermissionException extends Exception
{
	protected static string $defaultKey = 'permission';
	protected static string $defaultFallback = 'You are not allowed to do this';
	protected static int $defaultHttpCode = 403;
}
