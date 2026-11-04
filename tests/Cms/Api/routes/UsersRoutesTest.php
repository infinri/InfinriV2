<?php

namespace Infinri\Cms;

use Infinri\Exception\InvalidArgumentException;
use Infinri\Exception\NotFoundException;
use Infinri\Filesystem\Dir;
use Infinri\Form\Field;
use Infinri\TestCase;

class UsersRoutesTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = INFINRI_TMP_DIR . '/Cms.UsersRoutes';

	protected function setUp(): void
	{
		Blueprint::$loaded = [];

		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roles' => [
				[
					'name' => 'admin'
				],
				[
					'name' => 'editor'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'name'     => 'Bastian',
					'email'    => 'admin@infinri.com',
					'role'     => 'admin',
					'password' => password_hash('12345678', PASSWORD_DEFAULT)
				],
				[
					'name'     => 'Sonja',
					'email'    => 'editor@infinri.com',
					'role'     => 'admin',
					'password' => password_hash('87654321', PASSWORD_DEFAULT)
				]
			]
		]);

		$this->app->impersonate('infinri');
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		App::destroy();
		Field::$types = [];
		Section::$types = [];
		Dir::remove(static::TMP);
	}

	protected function setUpAppWithoutUserAccess(): App
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/restricted' => [
					'name'    => 'restricted',
					'options' => ['access' => false]
				]
			],
			'roles' => [
				[
					'name' => 'restricted'
				]
			],
			'users' => [
				[
					'name'  => 'Admin',
					'email' => 'admin@infinri.com',
					'role'  => 'admin'
				],
				[
					'name'  => 'Restricted',
					'email' => 'restricted@infinri.com',
					'role'  => 'restricted'
				]
			]
		]);

		$app->impersonate('admin@infinri.com');

		return $app;
	}

	public function testAvatar(): void
	{
		// create an avatar for the user
		$this->app->user('admin@infinri.com')->createFile([
			'filename' => 'profile.jpg',
			'source'   => static::FIXTURES . '/avatar.jpg',
			'template' => 'avatar',
		]);

		$response = $this->app->api()->call('users/admin@infinri.com/avatar');

		$this->assertSame('profile.jpg', $response['data']['filename']);
	}

	public function testAvatarDelete(): void
	{
		// create an avatar for the user
		$this->app->user('admin@infinri.com')->createFile([
			'filename' => 'profile.jpg',
			'source'   => static::FIXTURES . '/avatar.jpg',
			'template' => 'avatar',
		]);

		$response = $this->app->api()->call('users/admin@infinri.com/avatar', 'DELETE');

		$this->assertTrue($response);
	}

	public function testBlueprint(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'  => 'admin',
					'title' => 'Test'
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/admin@infinri.com/blueprint');

		$this->assertSame(200, $response['code']);
		$this->assertSame('Test', $response['data']['title']);
		$this->assertSame('admin', $response['data']['name']);
	}

	public function testBlueprints(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'     => 'admin',
					'title'    => 'Admin',
					'sections' => [
						'test' => [
							'type'   => 'pages',
							'parent' => 'site',
							'templates' => [
								'foo',
								'bar'
							]
						]
					]
				],
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'Editor'
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/admin@infinri.com/blueprints');

		$this->assertCount(2, $response);
		$this->assertSame('Foo', $response[0]['title']);
		$this->assertSame('Bar', $response[1]['title']);
	}

	public function testCreate(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users', 'POST', [
			'body' => [
				'email' => 'test@infinri.com',
				'role'  => 'admin'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('model', $response['type']);
		$this->assertSame('admin', $response['data']['role']['name']);
		$this->assertSame('test@infinri.com', $response['data']['username']);
	}

	public function testChangeEmail(): void
	{
		$response = $this->app->api()->call('users/admin@infinri.com/email', 'PATCH', [
			'body' => [
				'email' => 'admin@infinri.de'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('admin@infinri.de', $response['data']['email']);
	}

	public function testChangeLanguage(): void
	{
		$response = $this->app->api()->call('users/admin@infinri.com/language', 'PATCH', [
			'body' => [
				'language' => 'de'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('de', $response['data']['language']);
	}

	public function testChangeName(): void
	{
		$response = $this->app->api()->call('users/admin@infinri.com/name', 'PATCH', [
			'body' => [
				'name' => 'Test user'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('Test user', $response['data']['name']);
	}

	public function testChangePassword(): void
	{
		$this->app->impersonate('admin@infinri.com');

		$response = $this->app->api()->call('users/editor@infinri.com/password', 'PATCH', [
			'body' => [
				'currentPassword' => '12345678',
				'password'        => 'super-secure-new-password'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertTrue($this->app->user('editor@infinri.com')->validatePassword('super-secure-new-password'));
	}

	public function testChangePasswordMissingCurrentPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		$this->app->impersonate('admin@infinri.com');

		$this->app->api()->call('users/editor@infinri.com/password', 'PATCH', [
			'body' => [
				'password' => 'super-secure-new-password'
			]
		]);
	}

	public function testChangePasswordWrongCurrentPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');

		$this->app->impersonate('admin@infinri.com');

		$this->app->api()->call('users/editor@infinri.com/password', 'PATCH', [
			'body' => [
				'currentPassword' => 'definitely-not-correct',
				'password'        => 'super-secure-new-password'
			]
		]);
	}

	public function testChangePasswordReset(): void
	{
		// the password reset mode of the acting user must not take effect when
		// changing the password of a different user

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid password. Passwords must be at least 8 characters long.');

		$this->app->impersonate('admin@infinri.com');
		$this->app->session()->set(
			'infinri.resetPassword',
			$this->app->user()->id()
		);

		$this->app->api()->call('users/editor@infinri.com/password', 'PATCH', [
			'body' => [
				'password' => 'super-secure-new-password'
			]
		]);
	}

	public function testChangeRole(): void
	{
		$response = $this->app->api()->call('users/editor@infinri.com/role', 'PATCH', [
			'body' => [
				'role' => 'editor'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('editor', $response['data']['role']['name']);
	}

	public function testDelete(): void
	{
		$response = $this->app->api()->call('users/admin@infinri.com', 'DELETE');
		$this->assertTrue($response);
	}

	public function testDeleteWithoutAccess(): void
	{
		$app = $this->setUpAppWithoutUserAccess();

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "restricted@infinri.com" cannot be found');

		$app->api()->call('users/restricted@infinri.com', 'DELETE');
	}

	public function testFields(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'fields' => [
						'test' => [
							'type' => 'test'
						]
					]
				]
			],
			'fields' => [
				'test' => [
					'api' => fn () => [
						[
							'pattern' => '/',
							'action'  => fn () => 'Test home route'
						],
						[
							'pattern' => 'nested',
							'action'  => fn () => 'Test nested route'
						],
					]
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/admin@infinri.com/fields/test');

		$this->assertSame('Test home route', $response);

		$response = $app->api()->call('users/admin@infinri.com/fields/test/nested');

		$this->assertSame('Test nested route', $response);
	}

	public function testFile(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@infinri.com',
					'files' => [
						[
							'filename' => 'a.jpg',
						]
					]
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/test@infinri.com/files/a.jpg');

		$this->assertSame('a.jpg', $response['data']['filename']);
	}

	public function testFiles(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@infinri.com',
					'files' => [
						[
							'filename' => 'c.jpg',
						],
						[
							'filename' => 'a.jpg',
						],
						[
							'filename' => 'b.jpg',
						]
					]
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/test@infinri.com/files');

		$this->assertCount(3, $response['data']);
		$this->assertSame('a.jpg', $response['data'][0]['filename']);
		$this->assertSame('b.jpg', $response['data'][1]['filename']);
		$this->assertSame('c.jpg', $response['data'][2]['filename']);
	}

	public function testFilesSorted(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@infinri.com',
					'files' => [
						[
							'filename' => 'a.jpg',
							'content'  => [
								'sort' => 2
							]
						],
						[
							'filename' => 'b.jpg',
							'content'  => [
								'sort' => 1
							]
						]
					]
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/test@infinri.com/files');

		$this->assertSame('b.jpg', $response['data'][0]['filename']);
		$this->assertSame('a.jpg', $response['data'][1]['filename']);
	}

	public function testGet(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/admin@infinri.com');

		$this->assertSame('admin@infinri.com', $response['data']['email']);
	}

	public function testGetWithoutAccess(): void
	{
		$app = $this->setUpAppWithoutUserAccess();

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "restricted@infinri.com" cannot be found');

		$app->api()->call('users/restricted@infinri.com');
	}

	public function testRoles(): void
	{
		$this->app->impersonate('infinri');

		$response = $this->app->api()->call('users/admin@infinri.com/roles');

		$this->assertSame(200, $response['code']);

		$this->assertCount(2, $response['data']);
		$this->assertSame('admin', $response['data'][0]['name']);
		$this->assertSame('editor', $response['data'][1]['name']);
	}

	public function testSearchName(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/search', 'GET', [
			'query' => [
				'q' => 'Bastian'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('admin@infinri.com', $response['data'][0]['email']);
	}

	public function testSearchWithGetRequest(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/search', 'GET', [
			'query' => [
				'q' => 'editor'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('editor@infinri.com', $response['data'][0]['email']);
	}

	public function testSearchWithGetRequestWithoutAccess(): void
	{
		$app = $this->setUpAppWithoutUserAccess();

		$response = $app->api()->call('users/search', 'GET', [
			'query' => [
				'q' => 'Restricted'
			]
		]);

		$this->assertCount(0, $response['data']);
	}

	public function testSearchWithPostRequest(): void
	{
		$app = $this->app;

		$response = $app->api()->call('users/search', 'POST', [
			'body' => [
				'search' => 'editor'
			]
		]);

		$this->assertCount(1, $response['data']);
		$this->assertSame('editor@infinri.com', $response['data'][0]['email']);
	}

	public function testSearchWithPostRequestIgnoresFilterBy(): void
	{
		// filterBy role == editor would normally return only 1 user;
		// since filterBy is stripped from the body, both users are returned
		$response = $this->app->api()->call('users/search', 'POST', [
			'body' => [
				'filterBy' => [
					['field' => 'role', 'operator' => '==', 'value' => 'editor']
				]
			]
		]);

		$this->assertCount(2, $response['data']);
	}

	public function testSearchWithPostRequestIgnoresSortBy(): void
	{
		// sortBy in the body is stripped; default order (alphabetical asc) is preserved
		$response = $this->app->api()->call('users/search', 'POST', [
			'body' => [
				'sortBy' => 'email desc'
			]
		]);

		$this->assertCount(2, $response['data']);
		$this->assertSame('admin@infinri.com', $response['data'][0]['email']);
		$this->assertSame('editor@infinri.com', $response['data'][1]['email']);
	}

	public function testSearchWithPostRequestWithoutAccess(): void
	{
		$app = $this->setUpAppWithoutUserAccess();

		$response = $app->api()->call('users/search', 'POST', [
			'body' => [
				'search' => 'Restricted'
			]
		]);

		$this->assertCount(0, $response['data']);
	}

	public function testSearchWithPostRequestRespectsLimit(): void
	{
		$response = $this->app->api()->call('users/search', 'POST', [
			'body' => [
				'limit' => 1,
			]
		]);

		$this->assertCount(1, $response['data']);
	}

	public function testSearchWithPostRequestIgnoresNullValues(): void
	{
		// search: null should not restrict results
		$response = $this->app->api()->call('users/search', 'POST', [
			'body' => [
				'search' => null,
			]
		]);

		$this->assertCount(2, $response['data']);
	}

	public function testSearchWithPostRequestIgnoresNotKey(): void
	{
		// the 'not' key is stripped; both users are returned
		$response = $this->app->api()->call('users/search', 'POST', [
			'body' => [
				'not' => ['admin@infinri.com'],
			]
		]);

		$this->assertCount(2, $response['data']);
	}

	public function testSections(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'sections' => [
						'test' => [
							'type' => 'test'
						]
					]
				]
			],
			'sections' => [
				'test' => [
					'toArray' => fn () => [
						'foo' => 'bar'
					]
				]
			]
		]);

		$app->impersonate('infinri');

		$response = $app->api()->call('users/admin@infinri.com/sections/test');
		$expected = [
			'status' => 'ok',
			'code'   => 200,
			'name'   => 'test',
			'type'   => 'test',
			'foo'    => 'bar'
		];

		$this->assertSame($expected, $response);
	}

	public function testUpdate(): void
	{
		$response = $this->app->api()->call('users/admin@infinri.com', 'PATCH', [
			'body' => [
				'position' => 'Admin'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('Admin', $response['data']['content']['position']);
	}

	public function testUpdateWithoutAccess(): void
	{
		$app = $this->setUpAppWithoutUserAccess();

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "restricted@infinri.com" cannot be found');

		$app->api()->call('users/restricted@infinri.com', 'PATCH', [
			'body' => [
				'position' => 'Restricted'
			]
		]);
	}

	public function testUsers(): void
	{
		$response = $this->app->api()->call('users');

		$this->assertSame('admin@infinri.com', $response['data'][0]['email']);
		$this->assertSame('editor@infinri.com', $response['data'][1]['email']);
	}

	public function testUsersWithoutAccess(): void
	{
		$app = $this->setUpAppWithoutUserAccess();

		$response = $app->api()->call('users');

		$this->assertCount(1, $response['data']);
		$this->assertSame('admin@infinri.com', $response['data'][0]['email']);
	}
}
