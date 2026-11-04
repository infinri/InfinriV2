<?php

namespace Infinri\Panel\Areas;

class SiteTest extends AreaTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testPageWithoutAuthentication(): void
	{
		$this->assertRedirect('pages/home', 'login');
	}

	public function testPageWithMissingModel(): void
	{
		$this->login();
		$this->assertErrorView('pages/does-not-exist', 'The page "does-not-exist" cannot be found');
	}

	public function testPage(): void
	{
		$this->login();

		$this->app->site()->createChild([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$view  = $this->view('pages/test');
		$props = $view['props'];

		$this->assertSame('default', $props['blueprint']);
		$this->assertSame([
			'isLocked' => false,
			'modified' => null,
			'user'     => [
				'id'    => 'test',
				'email' => 'test@infinri.com'
			]
		], $props['lock']);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertSame([], $props['tabs']);

		$this->assertSame('Test', $props['versions']['latest']['title']);
		$this->assertSame('Test', $props['versions']['changes']['title']);

		$this->assertArrayNotHasKey('model', $props);
		$this->assertSame('test', $props['id']);
		$this->assertSame('Test', $props['title']);

		$this->assertNull($props['next']);
		$this->assertNull($props['prev']);
	}

	public function testPageFileWithoutModel(): void
	{
		$this->login();

		$this->app->site()->createChild([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$this->assertErrorView('pages/test/files/does-not-exist.jpg', 'The file "does-not-exist.jpg" cannot be found');
	}

	public function testPageFile(): void
	{
		$this->login();

		$this->app->site()->createChild([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			],
			'files' => [
				[
					'filename' => 'test.jpg',
					'template' => 'image'
				]
			]
		]);

		$view  = $this->view('pages/test/files/test.jpg');
		$props = $view['props'];

		$this->assertSame('image', $props['blueprint']);
		$this->assertSame([
			'isLocked' => false,
			'modified' => null,
			'user'     => [
				'id'    => 'test',
				'email' => 'test@infinri.com'
			]
		], $props['lock']);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertSame([], $props['tabs']);
		$this->assertSame([], $props['versions']['changes']);
		$this->assertSame([], $props['versions']['latest']);

		$this->assertArrayNotHasKey('model', $props);
		$this->assertSame('jpg', $props['extension']);
		$this->assertSame('test.jpg', $props['filename']);
		$this->assertSame('test/test.jpg', $props['id']);
		$this->assertSame('image/jpeg', $props['mime']);
		$this->assertSame('image', $props['type']);

		$this->assertNull($props['next']);
		$this->assertNull($props['prev']);
	}

	public function testPagePreview(): void
	{
		$this->login();

		$page = $this->app->site()->createChild([
			'slug'    => 'test',
			'isDraft' => false,
			'content' => [
				'title' => 'Test'
			]
		]);

		$view  = $this->view('pages/test/preview/changes');
		$props = $view['props'];

		$token = $page->version('changes')->previewToken();

		$this->assertSame('k-preview-view', $view['component']);
		$this->assertSame('Test | Preview', $view['title']);
		$this->assertSame('/test?_token=' . $token . '&_version=changes', $props['src']['changes']);
	}

	public function testSiteWithoutAuthentication(): void
	{
		$this->assertRedirect('site', 'login');
	}

	public function testSiteRedirectFromHome(): void
	{
		$this->login();
		$this->assertRedirect('/', 'site');
	}

	public function testSite(): void
	{
		$this->login();

		$site = $this->app->site();

		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$view  = $this->view('site');
		$props = $view['props'];

		$this->assertSame('site', $view['id']);
		$this->assertSame('Site', $view['title']);
		$this->assertSame('k-site-view', $view['component']);

		$this->assertSame('site', $props['blueprint']);
		$this->assertArrayNotHasKey('model', $props);
		$this->assertSame('/site', $props['link']);
		$this->assertSame('', $props['title']);
	}

	public function testSiteNotAccessible(): void
	{
		$this->app([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@infinri.com',
					'role'  => 'editor',
				]
			]
		]);

		$this->login('editor@infinri.com');
		$this->assertErrorView('site', 'The site is not accessible');
	}

	public function testSitePreviewNotAccessible(): void
	{
		$this->app([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@infinri.com',
					'role'  => 'editor',
				]
			]
		]);

		$this->login('editor@infinri.com');
		$this->assertErrorView('site/preview/changes', 'The site is not accessible');
	}

	public function testSiteFile(): void
	{
		$this->app([
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'template' => 'image'
					]
				]
			]
		]);

		$this->login();

		$view  = $this->view('site/files/test.jpg');
		$props = $view['props'];

		$this->assertSame('image', $props['blueprint']);
		$this->assertSame([
			'isLocked' => false,
			'modified' => null,
			'user'     => [
				'id'    => 'test',
				'email' => 'test@infinri.com'
			]
		], $props['lock']);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertSame([], $props['tabs']);

		$this->assertSame([], $props['versions']['changes']);
		$this->assertSame([], $props['versions']['latest']);
		$this->assertArrayNotHasKey('model', $props);
		$this->assertSame('jpg', $props['extension']);
		$this->assertSame('test.jpg', $props['filename']);
		$this->assertSame('test.jpg', $props['id']);
		$this->assertSame('image/jpeg', $props['mime']);
		$this->assertSame('image', $props['type']);

		$this->assertNull($props['next']);
		$this->assertNull($props['prev']);
	}

	public function testSitePreview(): void
	{
		$this->login();

		$site = $this->app->site();

		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$view  = $this->view('site/preview/changes');
		$props = $view['props'];

		$token = $site->version('changes')->previewToken();

		$this->assertSame('k-preview-view', $view['component']);
		$this->assertSame('Site | Preview', $view['title']);
		$this->assertSame('/?_token=' . $token . '&_version=changes', $props['src']['changes']);
	}

	public function testSiteTitle(): void
	{
		$this->app([
			'blueprints' => [
				'site' => [
					'title' => 'My Blog',
				]
			]
		]);

		$this->login();

		$view  = $this->view('site');

		$this->assertSame('site', $view['id']);
		$this->assertSame('My Blog', $view['title']);
	}

	public function testSiteTitleMultilang(): void
	{
		$this->app([
			'blueprints' => [
				'site' => [
					'title' => [
						'de' => 'Mein Blog',
						'en' => 'My Blog',
					],
				]
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
				],
				[
					'default' => true,
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			],
		]);

		$this->login();

		$view  = $this->view('site');

		$this->assertSame('site', $view['id']);
		$this->assertSame('Mein Blog', $view['title']);
	}
}
