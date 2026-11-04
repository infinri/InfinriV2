<?php

namespace Kirby\Exception;

class ErrorPageException extends Exception
{
	protected static string $defaultKey = 'errorPage';
	protected static string $defaultFallback = 'Triggered error page';
	protected static int $defaultHttpCode = 404;
}
