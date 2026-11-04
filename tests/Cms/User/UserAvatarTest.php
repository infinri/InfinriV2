<?php

namespace Infinri\Cms;

use Infinri\Exception\PermissionException;
use Infinri\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserAvatarTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/../Api/routes/fixtures';
	public const TMP = INFINRI_TMP_DIR . '/Cms.UserAvatar';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'admin@infinri.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@infinri.com'
		]);
	}

	protected function avatarSource(): string
	{
		$source = static::TMP . '/tmp-avatar.jpg';
		F::copy(static::FIXTURES . '/avatar.jpg', $source);
		return $source;
	}

	public function testCreateAvatar(): void
	{
		$user   = $this->app->user('admin@infinri.com');
		$result = $user->createAvatar($this->avatarSource(), 'jpg');

		$this->assertInstanceOf(User::class, $result);
		$this->assertFileExists($user->root() . '/profile.jpg');
	}

	public function testCreateAvatarHooks(): void
	{
		$calls   = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.createAvatar:before' => function (User $user, string $source, string $extension) use ($phpunit, &$calls) {
					$phpunit->assertSame('admin@infinri.com', $user->email());
					$phpunit->assertSame('jpg', $extension);
					$calls++;
				},
				'user.createAvatar:after' => function (User $user) use ($phpunit, &$calls) {
					$phpunit->assertSame('admin@infinri.com', $user->email());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('infinri');

		$this->app->user('admin@infinri.com')->createAvatar($this->avatarSource(), 'jpg');

		$this->assertSame(2, $calls);
	}

	public function testCreateAvatarWithoutPermission(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'user' => ['update' => false]
					]
				]
			],
			'user'  => 'editor@infinri.com',
			'users' => [
				['email' => 'admin@infinri.com', 'role' => 'admin'],
				['email' => 'editor@infinri.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the user "editor@infinri.com"');

		$this->app->user('editor@infinri.com')->createAvatar($this->avatarSource(), 'jpg');
	}

	public function testDeleteAvatar(): void
	{
		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');

		$this->assertFileExists($user->root() . '/profile.jpg');

		$result = $user->deleteAvatar();

		$this->assertTrue($result);
		$this->assertFileDoesNotExist($user->root() . '/profile.jpg');
	}

	public function testDeleteAvatarHooks(): void
	{
		$calls   = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.deleteAvatar:before' => function (User $user) use ($phpunit, &$calls) {
					$phpunit->assertSame('admin@infinri.com', $user->email());
					$calls++;
				},
				'user.deleteAvatar:after' => function (bool $status, User $user) use ($phpunit, &$calls) {
					$phpunit->assertTrue($status);
					$phpunit->assertSame('admin@infinri.com', $user->email());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('infinri');

		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');
		$user->deleteAvatar();

		$this->assertSame(2, $calls);
	}

	public function testDeleteAvatarWithoutPermission(): void
	{
		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'user' => ['update' => false]
					]
				]
			],
			'user'  => 'editor@infinri.com',
			'users' => [
				['email' => 'admin@infinri.com', 'role' => 'admin'],
				['email' => 'editor@infinri.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the user "editor@infinri.com"');

		$this->app->user('editor@infinri.com')->deleteAvatar();
	}

	public function testReplaceAvatar(): void
	{
		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');

		$this->assertFileExists($user->root() . '/profile.jpg');

		$result = $user->replaceAvatar($this->avatarSource(), 'jpg');

		$this->assertInstanceOf(User::class, $result);
		$this->assertFileExists($user->root() . '/profile.jpg');
	}

	public function testReplaceAvatarWithDifferentExtension(): void
	{
		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');

		$this->assertFileExists($user->root() . '/profile.jpg');

		$source = static::TMP . '/tmp-avatar.png';
		F::copy(static::FIXTURES . '/avatar.jpg', $source);

		$result = $user->replaceAvatar($source, 'png');

		$this->assertInstanceOf(User::class, $result);
		$this->assertFileDoesNotExist($user->root() . '/profile.jpg');
		$this->assertFileExists($user->root() . '/profile.png');
	}

	public function testReplaceAvatarWithoutPermission(): void
	{
		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'user' => ['update' => false]
					]
				]
			],
			'user'  => 'editor@infinri.com',
			'users' => [
				['email' => 'admin@infinri.com', 'role' => 'admin'],
				['email' => 'editor@infinri.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the user "editor@infinri.com"');

		$this->app->user('editor@infinri.com')->replaceAvatar($this->avatarSource(), 'jpg');
	}

	public function testReplaceAvatarHooks(): void
	{
		$calls   = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.replaceAvatar:before' => function (User $user, string $source, string $extension) use ($phpunit, &$calls) {
					$phpunit->assertSame('admin@infinri.com', $user->email());
					$phpunit->assertSame('jpg', $extension);
					$calls++;
				},
				'user.replaceAvatar:after' => function (User $user) use ($phpunit, &$calls) {
					$phpunit->assertSame('admin@infinri.com', $user->email());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('infinri');

		$user = $this->app->user('admin@infinri.com');
		$user->createAvatar($this->avatarSource(), 'jpg');
		$user->replaceAvatar($this->avatarSource(), 'jpg');

		$this->assertSame(2, $calls);
	}
}
