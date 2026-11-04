<?php

namespace Infinri\Http\Exceptions;

use Exception;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class NextRouteExceptionTest extends TestCase
{
	public function testException(): void
	{
		$exception = new NextRouteException(message: 'test');
		$this->assertInstanceOf(Exception::class, $exception);
		$this->assertSame('test', $exception->getMessage());
	}
}
