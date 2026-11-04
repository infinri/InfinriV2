<?php

namespace Infinri\Panel\Areas;

class LogoutTest extends AreaTestCase
{
	public const LOGIN = 'login?_globals=%24system%2C%24translation';

	protected function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testLogoutGuest(): void
	{
		$this->assertRedirect('logout', static::LOGIN);
	}

	public function testLogoutUser(): void
	{
		$this->login('test@infinri.com');
		$this->assertSame('test@infinri.com', $this->app->user()->email());

		$this->assertRedirect('logout', static::LOGIN);

		$this->assertNull($this->app->user());
	}

	public function testLogoutChallenge(): void
	{
		$this->app->session()->set('infinri.challenge.code', '123456');

		$this->assertRedirect('logout', static::LOGIN);

		$this->assertNull($this->app->session()->get('infinri.challenge.code'));
	}

	public function testLogoutCsrf(): void
	{
		// the Panel follows the redirect in place, so the login view
		// it lands on has to hand out a new CSRF token
		$this->app([
			'request' => [
				'query' => [
					'_json'    => true,
					'_globals' => '$system,$translation'
				]
			]
		]);

		$this->login('test@infinri.com');

		$token = $this->app->auth()->csrfFromSession();

		$this->assertRedirect('logout', static::LOGIN);
		$this->assertNull($this->app->session()->get('infinri.csrf'));

		$login = $this->response('login', true);

		$this->assertArrayHasKey('$system', $login);
		$this->assertNotSame($token, $login['$system']['csrf']);
	}
}
