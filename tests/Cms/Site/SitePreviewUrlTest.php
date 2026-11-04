<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class SitePreviewUrlTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.SitePreviewUrl';

	public static function previewUrlProvider(): array
	{
		return [
			[null, '/'],
			['https://test.com', 'https://test.com'],
			['{{ site.url }}#test', '/#test'],
			[false, null],
			[null, null, false],
		];
	}

	#[DataProvider('previewUrlProvider')]
	public function testCustomPreviewUrl(
		$input,
		$expected,
		bool $authenticated = true
	): void {
		$this->app = $this->app->clone([
			'urls' => [
				'index' => '/'
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@infinri.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		if ($authenticated) {
			$this->app->impersonate('test@infinri.com');
		}

		$options = [];

		if ($input !== null) {
			$options = [
				'preview' => $input
			];
		}

		// simple
		$site = new Site([
			'blueprint' => [
				'name'    => 'site',
				'options' => $options
			],
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertSame($expected, $site->previewUrl());
	}

	public function testPreviewUrlWithoutHomePage(): void
	{
		$this->app->impersonate('infinri');

		$site = new Site();

		// only `site.preview` is consulted, not the home page
		$this->assertNotNull($site->previewUrl());
	}

	public function testPreviewUrlIgnoresHomePagePermission(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@infinri.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'          => 'editor',
					'name'        => 'editor',
					'permissions' => [
						'pages' => [
							'preview' => false
						]
					]
				]
			]
		]);

		$this->app->impersonate('test@infinri.com');

		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		// `pages.preview` of the home page no longer affects the site
		$this->assertNotNull($site->previewUrl());
		$this->assertNotNull($site->previewUrl('latest'));
		$this->assertNotNull($site->previewUrl('changes'));
	}

	public function testPreviewUrlMissingSitePermission(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@infinri.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'          => 'editor',
					'name'        => 'editor',
					'permissions' => [
						'site' => [
							'preview' => false
						]
					]
				]
			]
		]);

		$this->app->impersonate('test@infinri.com');

		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertNull($site->previewUrl());
		$this->assertNull($site->previewUrl('latest'));
		$this->assertNull($site->previewUrl('changes'));
	}

	public function testPreviewUrlDisabledByBlueprintOptions(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@infinri.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'   => 'editor',
					'name' => 'editor',
				]
			]
		]);

		$this->app->impersonate('test@infinri.com');

		// disabling the whole options block also disables preview
		$site = new Site([
			'blueprint' => [
				'name'    => 'site',
				'options' => false
			],
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertNull($site->previewUrl());
	}

	public function testPreviewUrlUnauthenticated(): void
	{
		// log out
		$this->app->impersonate();

		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertNull($site->previewUrl());
	}
}
