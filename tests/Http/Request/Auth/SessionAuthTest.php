<?php

namespace Infinri\Http\Request\Auth;

use Infinri\Cms\App;
use Infinri\Filesystem\Dir;
use Infinri\Http\Request\Auth;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
#[CoversClass(SessionAuth::class)]
class SessionAuthTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Http.Request.Auth.SessionAuth';

	protected function setUp(): void
	{
		Dir::make(static::TMP . '/site/sessions');

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
	}

	public function testInstance(): void
	{
		$session = $this->app->session();
		$session->ensureToken();

		$auth = new SessionAuth($session->token());

		$this->assertSame('session', $auth->type());
		$this->assertSame($session->token(), $auth->data());
		$this->assertSame($session->token(), $auth->token());
		$this->assertSame($session, $auth->session());
		$this->assertSame('Session ' . $session->token(), (string)$auth);
	}
}
