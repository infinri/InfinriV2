<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserPicker::class)]
class UserPickerTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.UserPicker';

	protected function setUp(): void
	{
		parent::setUp();

		// use a uuid-based role to avoid static permission cache
		// collisions with roles used in other tests
		$uuid = uuid();

		$this->app = $this->app->clone([
			'users' => [
				['email' => 'a@infinri.com', 'role' => 'editor-' . $uuid],
				['email' => 'b@infinri.com', 'role' => 'editor-' . $uuid],
				['email' => 'c@infinri.com', 'role' => 'editor-' . $uuid]
			]
		]);

		$this->app->impersonate('infinri');
	}

	public function testDefaults(): void
	{
		$picker = new UserPicker();

		$this->assertCount(3, $picker->items());
	}

	public function testNonListableUsersAreFiltered(): void
	{
		// use a uuid-based role to avoid static permission cache
		// collisions with roles used in other tests
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'name'    => 'restricted-' . $uuid,
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'restricted-' . $uuid]
			],
			'users' => [
				[
					'email' => 'a@infinri.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'b@infinri.com',
					'role'  => 'restricted-' . $uuid
				],
				[
					'email' => 'c@infinri.com',
					'role'  => 'restricted-' . $uuid
				]
			]
		]);

		// the infinri superuser bypasses all blueprint restrictions
		$app->impersonate('infinri');

		$picker = new UserPicker();

		$this->assertCount(3, $picker->items());

		// the editor can only access their own account
		$app->impersonate('a@infinri.com');

		$picker = new UserPicker();

		// only the listable user is returned
		$this->assertCount(1, $picker->items());
		$this->assertSame('a@infinri.com', $picker->items()->first()->email());

	}

	public function testQuery(): void
	{
		$picker = new UserPicker([
			'query' => 'infinri.users.offset(1)'
		]);

		$this->assertCount(2, $picker->items());
	}
}
