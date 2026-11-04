<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserRoleTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.UserRole';

	public function testIsAdmin(): void
	{
		$user = new User([
			'email' => 'test@infinri.com',
			'role'  => 'admin'
		]);

		$this->assertTrue($user->isAdmin());

		$user = new User([
			'email' => 'test@infinri.com',
			'role'  => 'editor'
		]);

		$this->assertFalse($user->isAdmin());
	}

	public function testIsInfinri(): void
	{
		$user = new User([
			'id'   => 'infinri',
			'role' => 'admin'
		]);
		$this->assertTrue($user->isInfinri());

		$user = new User([
			'role' => 'admin'
		]);
		$this->assertFalse($user->isInfinri());

		$user = new User([
			'id'   => 'infinri',
		]);
		$this->assertFalse($user->isInfinri());

		$user = new User([
			'emai' => 'infinri@infinri.com',
		]);
		$this->assertFalse($user->isInfinri());
	}

	public function testIsNobody(): void
	{
		$user = new User([
			'id'   => 'nobody',
			'role' => 'nobody'
		]);
		$this->assertTrue($user->isNobody());

		$user = new User([
			'role' => 'nobody'
		]);
		$this->assertFalse($user->isNobody());

		$user = new User([
			'id' => 'nobody',
		]);
		$this->assertTrue($user->isNobody());
	}

	public function testRole(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
			],
			'users' => [
				[
					'email' => 'admin@infinri.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@infinri.com',
					'role'  => 'editor'
				],
				[
					'email' => 'foo@infinri.com',
					'role'  => 'foo'
				]
			],
		]);

		$user  = $this->app->user('editor@infinri.com');
		$this->assertSame('editor', $user->role()->id());

		$user  = $this->app->user('admin@infinri.com');
		$this->assertSame('admin', $user->role()->id());

		// non-existing role
		$user  = $this->app->user('foo@infinri.com');
		$this->assertSame('nobody', $user->role()->id());
	}
}
