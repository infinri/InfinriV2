<?php

namespace Infinri\Exception;

use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class AuthExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new AuthException();
		$this->assertSame('error.auth', $exception->getKey());
		$this->assertSame('Unauthenticated', $exception->getMessage());
		$this->assertSame(401, $exception->getHttpCode());
	}
}
