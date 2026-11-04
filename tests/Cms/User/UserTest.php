<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.User';

	public function testAvatar(): void
	{
		$user = new User([
			'email' => 'user@domain.com'
		]);

		$this->assertNull($user->avatar());
	}

	public function testCredentials(): void
	{
		$user = new User([
			'credentials' => $credentials = [
				'role' => 'editor'
			]
		]);

		$this->assertSame($credentials, $user->credentials());
	}

	public function testId(): void
	{
		$user = new User([
			'id'    => 'test',
			'email' => 'user@domain.com'
		]);
		$this->assertSame('test', $user->id());
	}

	public function testSiblings(): void
	{
		$user = new User(['email' => 'user@domain.com']);
		$this->assertInstanceOf(Users::class, $user->siblings());
		$this->assertCount(0, $user->siblings());
	}

	public function testToString(): void
	{
		$user = new User([
			'email' => 'test@infinri.com'
		]);

		$this->assertSame('test@infinri.com', $user->toString());
	}

	public function testToStringWithTemplate(): void
	{
		$user = new User([
			'email' => 'test@infinri.com'
		]);

		$this->assertSame(
			'Email: test@infinri.com',
			$user->toString('Email: {{ user.email }}')
		);
	}

	public function testQuery(): void
	{
		$user = new User([
			'email' => 'test@infinri.com',
			'name'  => 'Test User'
		]);

		$this->assertSame('Test User', $user->query('user.name')->value());
		$this->assertSame('test@infinri.com', $user->query('user.email'));

		// also test with `model` key
		$this->assertSame('Test User', $user->query('model.name')->value());
		$this->assertSame('test@infinri.com', $user->query('model.email'));
	}
}
