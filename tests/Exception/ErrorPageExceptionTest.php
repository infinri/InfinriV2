<?php

namespace Infinri\Exception;

use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class ErrorPageExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new ErrorPageException();
		$this->assertSame('error.errorPage', $exception->getKey());
		$this->assertSame('Triggered error page', $exception->getMessage());
		$this->assertSame(404, $exception->getHttpCode());
	}
}
