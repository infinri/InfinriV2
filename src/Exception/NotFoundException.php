<?php

namespace Kirby\Exception;

class NotFoundException extends Exception
{
	protected static string $defaultKey = 'notFound';
	protected static string $defaultFallback = 'Not found';
	protected static int $defaultHttpCode = 404;
}
