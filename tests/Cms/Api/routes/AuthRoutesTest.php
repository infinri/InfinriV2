<?php

namespace Infinri\Cms;

use Infinri\Exception\InvalidArgumentException;
use Infinri\Filesystem\Dir;
use Infinri\TestCase;

class AuthRoutesTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.AuthRoutes';

	protected function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => static::TMP
			],
		]);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
	}

	public function testGet(): void
	{
		$this->app->impersonate('infinri');

		$response = $this->app->api()->call('auth');

		$this->assertSame('infinri@infinri.com', $response['data']['email']);
	}

	public function testLoginWithoutCSRF(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid CSRF token');

		$response = $this->app->api()->call('auth/login', 'POST');
	}
}
