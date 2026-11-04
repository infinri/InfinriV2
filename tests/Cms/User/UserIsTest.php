<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserIsTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.UserIs';

	public function testIsAccessibleUsersUser()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-global',
					'permissions' => [
						'users' => [
							'access' => false
						],
					]
				],
				[
					'name' => 'editor-own',
					'permissions' => [
						'user' => [
							'access' => false
						],
					]
				],
				[
					'name' => 'editor-both',
					'permissions' => [
						'user' => [
							'access' => false
						],
						'users' => [
							'access' => false
						],
					]
				]
			],
			'users' => [
				[
					'email' => 'editor-global@infinri.com',
					'role'  => 'editor-global'
				],
				[
					'email' => 'editor-own@infinri.com',
					'role'  => 'editor-own'
				],
				[
					'email' => 'editor-both@infinri.com',
					'role'  => 'editor-both'
				]
			],
		]);

		$userGlobal = $app->user('editor-global@infinri.com');
		$userOwn    = $app->user('editor-own@infinri.com');
		$userBoth   = $app->user('editor-both@infinri.com');

		// user with only `users.access` disabled can access themselves
		$app->impersonate('editor-global@infinri.com');
		$this->assertTrue($userGlobal->isAccessible());
		$this->assertFalse($userOwn->isAccessible());
		$this->assertFalse($userBoth->isAccessible());

		// users with only `user.access` disabled can access everyone else
		$app->impersonate('editor-own@infinri.com');
		$this->assertTrue($userGlobal->isAccessible());
		$this->assertFalse($userOwn->isAccessible());
		$this->assertTrue($userBoth->isAccessible());

		// users with both disabled can't access anything
		$app->impersonate('editor-both@infinri.com');
		$this->assertFalse($userGlobal->isAccessible());
		$this->assertFalse($userOwn->isAccessible());
		$this->assertFalse($userBoth->isAccessible());

		// almighty Infinri user can access everything
		$app->impersonate('infinri');
		$this->assertTrue($userGlobal->isAccessible());
		$this->assertTrue($userOwn->isAccessible());
		$this->assertTrue($userBoth->isAccessible());
	}

	public function testIsAccessibleBlueprint()
	{
		$app = new App([
			'blueprints' => [
				'users/editor-other' => [
					'options' => ['access' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor'
				],
				[
					'name' => 'editor-other'
				]
			],
			'users' => [
				[
					'email' => 'editor@infinri.com',
					'role'  => 'editor'
				],
				[
					'email' => 'editor-other@infinri.com',
					'role'  => 'editor-other'
				]
			],
		]);

		$user      = $app->user('editor@infinri.com');
		$userOther = $app->user('editor-other@infinri.com');

		// no access to role that has the option disabled
		$app->impersonate('editor@infinri.com');
		$this->assertTrue($user->isAccessible());
		$this->assertFalse($userOther->isAccessible());

		// almighty Infinri user can access everything
		$app->impersonate('infinri');
		$this->assertTrue($user->isAccessible());
		$this->assertTrue($userOther->isAccessible());
	}

	public function testIsListableUsersUser()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-global',
					'permissions' => [
						'users' => [
							'list' => false
						],
					]
				],
				[
					'name' => 'editor-own',
					'permissions' => [
						'user' => [
							'list' => false
						],
					]
				],
				[
					'name' => 'editor-both',
					'permissions' => [
						'user' => [
							'list' => false
						],
						'users' => [
							'list' => false
						],
					]
				]
			],
			'users' => [
				[
					'email' => 'editor-global@infinri.com',
					'role'  => 'editor-global'
				],
				[
					'email' => 'editor-own@infinri.com',
					'role'  => 'editor-own'
				],
				[
					'email' => 'editor-both@infinri.com',
					'role'  => 'editor-both'
				]
			],
		]);

		$userGlobal = $app->user('editor-global@infinri.com');
		$userOwn    = $app->user('editor-own@infinri.com');
		$userBoth   = $app->user('editor-both@infinri.com');

		// user with only `users.list` disabled can list themselves
		$app->impersonate('editor-global@infinri.com');
		$this->assertTrue($userGlobal->isListable());
		$this->assertFalse($userOwn->isListable());
		$this->assertFalse($userBoth->isListable());

		// users with only `user.list` disabled can list everyone else
		$app->impersonate('editor-own@infinri.com');
		$this->assertTrue($userGlobal->isListable());
		$this->assertFalse($userOwn->isListable());
		$this->assertTrue($userBoth->isListable());

		// users with both disabled can't list anything
		$app->impersonate('editor-both@infinri.com');
		$this->assertFalse($userGlobal->isListable());
		$this->assertFalse($userOwn->isListable());
		$this->assertFalse($userBoth->isListable());

		// almighty Infinri user can list everything
		$app->impersonate('infinri');
		$this->assertTrue($userGlobal->isListable());
		$this->assertTrue($userOwn->isListable());
		$this->assertTrue($userBoth->isListable());
	}

	public function testIsListableBlueprint()
	{
		$app = new App([
			'blueprints' => [
				'users/editor-other' => [
					'options' => ['list' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor'
				],
				[
					'name' => 'editor-other'
				]
			],
			'users' => [
				[
					'email' => 'editor@infinri.com',
					'role'  => 'editor'
				],
				[
					'email' => 'editor-other@infinri.com',
					'role'  => 'editor-other'
				]
			],
		]);

		$user      = $app->user('editor@infinri.com');
		$userOther = $app->user('editor-other@infinri.com');

		// no access to role that has the option disabled
		$app->impersonate('editor@infinri.com');
		$this->assertTrue($user->isListable());
		$this->assertFalse($userOther->isListable());

		// almighty Infinri user can access everything
		$app->impersonate('infinri');
		$this->assertTrue($user->isListable());
		$this->assertTrue($userOther->isListable());
	}

	public function testIsListableDependentOnAccess()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-both',
					'permissions' => [
						'user' => [
							'access' => false
						],
						'users' => [
							'access' => false
						],
					]
				],
			],
			'users' => [
				[
					'email' => 'editor-both@infinri.com',
					'role'  => 'editor-both'
				]
			],
		]);

		$user = $app->user('editor-both@infinri.com');

		$app->impersonate('editor-both@infinri.com');
		$this->assertFalse($user->isListable());

		$app->impersonate('infinri');
		$this->assertTrue($user->isListable());
	}
}
