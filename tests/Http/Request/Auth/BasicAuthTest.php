<?php

namespace Infinri\Http\Request\Auth;

use Infinri\Http\Request\Auth;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
#[CoversClass(BasicAuth::class)]
class BasicAuthTest extends TestCase
{
	public function testInstance(): void
	{
		$auth = new BasicAuth($data = base64_encode($credentials = 'testuser:testpass'));

		$this->assertSame('basic', $auth->type());
		$this->assertSame($data, $auth->data());
		$this->assertSame($credentials, $auth->credentials());
		$this->assertSame('testpass', $auth->password());
		$this->assertSame('testuser', $auth->username());
		$this->assertSame('Basic ' . $data, (string)$auth);
	}
}
