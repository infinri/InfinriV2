<?php

namespace Infinri\Cms;

use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LanguagePermissions::class)]
#[CoversClass(ModelPermissions::class)]
class LanguagePermissionsTest extends TestCase
{
	public static function actionProvider(): array
	{
		return [
			['create'],
			['delete'],
			['update'],
		];
	}

	#[DataProvider('actionProvider')]
	public function testWithAdmin($action): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$infinri->impersonate('infinri');

		$language = new Language(['code' => 'en']);
		$perms    = $language->permissions();

		$this->assertTrue($perms->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNobody($action): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$language = new Language(['code' => 'en']);
		$perms    = $language->permissions();

		$this->assertFalse($perms->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNoAdmin($action): void
	{
		$app = new App([
			'languages' => [
				[
					'code' => 'en'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				[
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'create' => false,
							'delete' => false,
							'update' => false
						],
					]
				]
			],
			'user'  => 'editor@infinri.com',
			'users' => [
				[
					'email' => 'admin@infinri.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@infinri.com',
					'role'  => 'editor'
				]
			],
		]);

		$language = $app->language('en');
		$perms    = $language->permissions();

		$this->assertSame('editor', $app->role()->name());
		$this->assertFalse($perms->can($action));
	}

	public function testCanDeleteWhenNotDeletable(): void
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin']
			]
		]);

		$app->impersonate('infinri');

		$language = $app->language('en');
		$perms    = $language->permissions();

		$this->assertFalse($perms->can('delete'));
	}
}
