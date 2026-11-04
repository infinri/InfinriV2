<?php

namespace Infinri\Panel;

use Infinri\Cms\App;
use Infinri\Cms\Site as ModelSite;
use Infinri\Filesystem\Dir;
use Infinri\Filesystem\F;
use Infinri\TestCase;
use Infinri\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Infinri\Panel\Site::class)]
#[CoversClass(Model::class)]
class SiteTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP = INFINRI_TMP_DIR . '/Panel.Site';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	protected function panel(array $props = [])
	{
		$site = new ModelSite($props);
		return new Site($site);
	}

	public function testButtons(): void
	{
		$this->assertSame([
			'k-open-view-button',
			'k-preview-view-button',
			'k-languages-view-button',
		], array_column($this->panel()->buttons(), 'component'));
	}

	public function testDropdownOptionIsNotOverridden(): void
	{
		$declaring = (new \ReflectionMethod(Site::class, 'dropdownOption'))->getDeclaringClass()->getName();

		$this->assertNotSame(Site::class, $declaring);
	}

	public function testImage(): void
	{
		$this->app->impersonate('infinri');

		$panel = $this->panel([
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		// fallback to model itself
		$image = $panel->image();
		$this->assertTrue(Str::endsWith($image['url'], '/test.jpg'));
	}

	public function testImageCover(): void
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);

		$app->impersonate('infinri');

		$site = $app->site();

		$testImage = static::FIXTURES . '/image/test.jpg';
		F::copy($testImage, $site->root() . '/test.jpg');

		$panel = new Site($site);

		$hash = $site->image()->mediaHash();
		$mediaUrl = $site->mediaUrl() . '/' . $hash;

		// cover disabled as default
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'gray-500',
			'cover' => false,
			'icon' => 'page',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'gray-500',
			'cover' => true,
			'icon' => 'page',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x38-crop.jpg 1x, ' . $mediaUrl . '/test-76x76-crop.jpg 2x'
		], $panel->image(['cover' => true]));
	}

	public function testPath(): void
	{
		$this->assertSame('site', $this->panel()->path());
	}

	public function testProps(): void
	{
		$props = $this->panel()->props();

		$this->assertArrayNotHasKey('model', $props);
		$this->assertArrayHasKey('title', $props);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('versions', $props);
	}

	public function testPreviewPermissionsWithoutHomePage(): void
	{
		$this->app->impersonate('infinri');

		$props = $this->app->site()->panel()->props();

		// only `site.preview` is consulted
		$this->assertTrue($props['permissions']['preview']);
	}

	public function testPreviewPermissionsWithHomePage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		// without logged in user
		$props = $this->app->site()->panel()->props();

		$this->assertFalse($props['permissions']['preview']);

		// with logged in user
		$this->app->impersonate('infinri');

		$props = $this->app->site()->panel()->props();

		$this->assertTrue($props['permissions']['preview']);
	}

	public function testPreviewPermissionsWithoutSitePermission(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
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

		// the site's own `site.preview` is disabled
		$props = $this->app->site()->panel()->props();

		$this->assertFalse($props['permissions']['preview']);
	}

	public function testView(): void
	{
		$view = $this->panel()->view();
		$this->assertArrayHasKey('props', $view);
		$this->assertSame('k-site-view', $view['component']);
	}
}
