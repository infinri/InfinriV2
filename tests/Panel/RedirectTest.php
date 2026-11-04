<?php

namespace Infinri\Panel;

use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Redirect::class)]
class RedirectTest extends TestCase
{
	public function testCode(): void
	{
		// default
		$redirect = new Redirect('https://infinri.com');
		$this->assertSame(302, $redirect->code());

		// valid code
		$redirect = new Redirect('https://infinri.com', 301);
		$this->assertSame(301, $redirect->code());

		// invalid code
		$redirect = new Redirect('https://infinri.com', 404);
		$this->assertSame(302, $redirect->code());
	}

	public function testLocation(): void
	{
		$redirect = new Redirect('https://infinri.com');
		$this->assertSame('https://infinri.com', $redirect->location());
	}

	public function testRefresh(): void
	{
		$redirect = new Redirect('https://infinri.com', 302, 5);
		$this->assertSame('https://infinri.com', $redirect->location());
		$this->assertSame(302, $redirect->code());
		$this->assertSame(5, $redirect->refresh());
	}
}
