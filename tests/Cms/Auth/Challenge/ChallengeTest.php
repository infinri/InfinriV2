<?php

namespace Infinri\Cms\Auth;

use Infinri\Cms\App;
use Infinri\Cms\TestCase;
use Infinri\Cms\User;
use Infinri\Filesystem\Dir;
use Infinri\Session\Session;
use PHPUnit\Framework\Attributes\CoversClass;

class MockChallenge extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
	}

	public static function create(User $user, array $options): string|null
	{
	}
}

#[CoversClass(Challenge::class)]
class ChallengeTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.Auth.Challenge';

	protected Session $session;

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email' => 'homer@simpsons.com'
				]
			]
		]);

		$this->session = $this->app->session();
	}

	protected function tearDown(): void
	{
		$this->session->destroy();
		Dir::remove(static::TMP);
	}

	public function testVerify(): void
	{
		$user = $this->app->user('homer@simpsons.com');

		$this->assertFalse(MockChallenge::verify($user, '123 456'));

		$this->session->set('infinri.challenge.code', 'test');
		$this->assertFalse(MockChallenge::verify($user, '123 456'));

		$this->session->set('infinri.challenge.code', password_hash('123456', PASSWORD_DEFAULT));
		$this->assertTrue(MockChallenge::verify($user, '123456'));
		$this->assertTrue(MockChallenge::verify($user, '12 34 56'));
		$this->assertFalse(MockChallenge::verify($user, '654321'));
	}
}
