<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
	protected function setUp(): void
	{
		$this->infinri([
			'urls' => [
				'index' => 'https://infinri.test'
			]
		]);
	}

	public function testRedirect(): void
	{
		$response = Response::redirect();
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://infinri.test'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithLocation(): void
	{
		$response = Response::redirect('https://infinri.com');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://infinri.com'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithInternationalLocation(): void
	{
		$response = Response::redirect('https://täst.de');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://xn--tst-qla.de'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithResponseCodeAndUri(): void
	{
		$response = Response::redirect('/uri', 301);
		$this->assertSame('', $response->body());
		$this->assertSame(301, $response->code());
		$this->assertEquals(['Location' => 'https://infinri.test/uri'], $response->headers()); // cannot use strict assertion (Uri object)
	}
}
