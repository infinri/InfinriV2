<?php

namespace Infinri\Cms;

use Infinri\Exception\PermissionException;
use Infinri\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserAuthTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.UserAuth';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@infinri.com',
					'id'    => 'testuser',
					'role'  => 'admin'
				]
			],
		]);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		parent::tearDown();
	}

	public function testIsLoggedIn(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'a@infinri.com'],
				['email' => 'b@infinri.com']
			],
		]);

		$a = $this->app->user('a@infinri.com');
		$b = $this->app->user('b@infinri.com');

		$this->assertFalse($a->isLoggedIn());
		$this->assertFalse($b->isLoggedIn());

		$this->app->impersonate('a@infinri.com');

		$this->assertTrue($a->isLoggedIn());
		$this->assertFalse($b->isLoggedIn());

		$this->app->impersonate('b@infinri.com');

		$this->assertFalse($a->isLoggedIn());
		$this->assertTrue($b->isLoggedIn());
	}

	public function testLoginLogout(): void
	{
		$user = $this->app->user('test@infinri.com');

		$this->assertNull($this->app->user());
		$user->loginPasswordless();
		$this->assertSame($user, $this->app->user());
		$user->logout();
		$this->assertNull($this->app->user());
	}

	public function testLoginLogoutHooks(): void
	{
		$calls         = 0;
		$phpunit       = $this;
		$logoutSession = false;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.login:before' => function ($user, $session) use ($phpunit, &$calls) {
					$phpunit->assertSame('test@infinri.com', $user->email());
					$phpunit->assertSame($session, S::instance());

					$calls += 1;
				},
				'user.login:after' => function ($user, $session) use ($phpunit, &$calls) {
					$phpunit->assertSame('test@infinri.com', $user->email());
					$phpunit->assertSame($session, S::instance());

					$calls += 2;
				},
				'user.logout:before' => function ($user, $session) use ($phpunit, &$calls) {
					$phpunit->assertSame('test@infinri.com', $user->email());
					$phpunit->assertSame($session, S::instance());

					$calls += 4;
				},
				'user.logout:after' => function ($user, $session) use ($phpunit, &$calls, &$logoutSession) {
					$phpunit->assertSame('test@infinri.com', $user->email());

					if ($logoutSession === true) {
						$phpunit->assertSame($session, S::instance());
						$phpunit->assertSame('value', S::instance()->get('some'));
					} else {
						$phpunit->assertNull($session);
					}

					$calls += 8;
				}
			]
		]);

		// without prepopulated session
		$user = $this->app->user('test@infinri.com');
		$user->loginPasswordless();
		$user->logout();

		// with a session with another value
		S::instance()->set('some', 'value');
		$logoutSession = true;
		$user->loginPasswordless();
		$user->logout();

		// each hook needs to be called exactly twice
		$this->assertSame((1 + 2 + 4 + 8) * 2, $calls);
	}

	public function testLoginPasswordlessInfinri(): void
	{
		$user = new User(['id' => 'infinri']);
		$this->expectException(PermissionException::class);
		$user->loginPasswordless();
	}

	public function testSessionData(): void
	{
		$user    = $this->app->user('test@infinri.com');
		$session = $this->app->session();

		$this->assertSame([], $session->data()->get());
		$user->loginPasswordless();
		$this->assertSame(['infinri.userId' => 'testuser'], $session->data()->get());
		$user->logout();
		$this->assertSame([], $session->data()->get());
	}

	public function testSessionDataWithPassword(): void
	{
		F::write(static::TMP . '/site/accounts/testuser/.htpasswd', 'a very secure hash');

		$user    = $this->app->user('test@infinri.com');
		$session = $this->app->session();

		$this->assertSame([], $session->data()->get());
		$user->loginPasswordless();
		$this->assertSame(['infinri.userId' => 'testuser', 'infinri.loginTimestamp' => 1337000000], $session->data()->get());
		$user->logout();
		$this->assertSame([], $session->data()->get());
	}
}
