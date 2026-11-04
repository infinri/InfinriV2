<?php

namespace Infinri\Panel;

use Infinri\Cms\App;
use Infinri\Cms\Blueprint;
use Infinri\Cms\User as ModelUser;
use Infinri\Content\Lock;
use Infinri\Filesystem\Dir;
use Infinri\Filesystem\F;
use Infinri\TestCase;
use Infinri\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

class UserForceLocked extends ModelUser
{
	public function lock(): Lock
	{
		return new Lock(
			user: new ModelUser(['email' => 'test@infinri.com']),
			modified: time()
		);
	}
}

#[CoversClass(\Infinri\Panel\User::class)]
#[CoversClass(Model::class)]
class UserTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP = INFINRI_TMP_DIR . '/Panel.User';

	protected function setUp(): void
	{
		Blueprint::$loaded = [];

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
	}

	protected function panel(array $props = [])
	{
		$user = new ModelUser(['id' => 'test', ...$props]);
		return new User($user);
	}

	public function testBreadcrumb(): void
	{
		$model = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$breadcrumb = (new User($model))->breadcrumb();
		$this->assertSame('test@infinri.com', $breadcrumb[0]['label']);
		$this->assertStringStartsWith('/users/', $breadcrumb[0]['link']);
	}

	public function testButtons(): void
	{
		$this->assertSame([
			'k-theme-view-button',
			'k-settings-view-button',
			'k-languages-view-button',
		], array_column($this->panel()->buttons(), 'component'));
	}

	public function testDropdownTotp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'auth' => [
					'methods' => ['password' => ['2fa' => true]]
				]
			],
			'users' => [
				['email' => 'test@infinri.com'],
				['email' => 'foo@infinri.com']
			],
			'user' => 'test@infinri.com'
		]);

		$user = $this->app->user();
		$dropdown = $user->panel()->dropdown();
		$this->assertSame('/account/totp/enable', $dropdown[7]['dialog']);
		$this->assertSame('qr-code', $dropdown[7]['icon']);

		$user->changeTotp('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$dropdown = $user->panel()->dropdown();
		$this->assertSame('/account/totp/disable', $dropdown[7]['dialog']);
	}

	public function testDropdownOptionIsNotOverridden(): void
	{
		$declaring = (new \ReflectionMethod(User::class, 'dropdownOption'))->getDeclaringClass()->getName();

		$this->assertNotSame(User::class, $declaring);
	}

	public function testHome(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$panel = new User($user);
		$this->assertSame('/panel/site', $panel->home());
	}

	public function testHomeWithCustomPath(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'home' => '/blog'
				]
			]
		]);

		$user = new ModelUser([
			'email' => 'test@infinri.com',
			'role'  => 'editor'
		]);

		$panel = new User($user);
		$this->assertSame('/blog', $panel->home());
	}

	public function testHomeWithCustomPathQuery(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'home' => '{{ site.find("test").panel.url }}'
				]
			]
		]);

		$user = new ModelUser([
			'email' => 'test@infinri.com',
			'role'  => 'editor'
		]);

		$panel = new User($user);
		$this->assertSame('/panel/pages/test', $panel->home());
	}

	public function testImage(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$image = (new User($user))->image();
		$this->assertSame('user', $image['icon']);
		$this->assertFalse(isset($image['url']));
	}

	public function testImageStringQuery(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		// fallback to model itself
		$image = (new User($user))->image('foo.bar');
		$this->assertNotEmpty($image);
	}

	public function testImageCover(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@infinri.com',
					'files' => [
						[
							'filename' => 'test.jpg',
							'template' => 'avatar'
						]
					]
				]
			]
		]);

		$user = $app->user('test@infinri.com');

		$testImage = static::FIXTURES . '/image/test.jpg';
		F::copy($testImage, $user->root() . '/test.jpg');

		$panel = new User($user);

		$hash = $user->image()->mediaHash();
		$mediaUrl = $user->mediaUrl() . '/' . $hash;

		// cover disabled as default
		$this->assertSame([
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => false,
			'icon' => 'user',
			'ratio' => '1/1',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back' => 'black',
			'color' => 'gray-500',
			'cover' => true,
			'icon' => 'user',
			'ratio' => '1/1',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x38-crop.jpg 1x, ' . $mediaUrl . '/test-76x76-crop.jpg 2x'
		], $panel->image(['cover' => true]));
	}

	public function testOptions(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$user->infinri()->impersonate('infinri');

		$expected = [
			'access'         => true,
			'create'         => true,
			'changeEmail'    => true,
			'changeLanguage' => true,
			'changeName'     => true,
			'changePassword' => true,
			'changeRole'     => true,
			'delete'         => true,
			'list'           => true,
			'update'         => true,
		];

		$panel = new User($user);
		$this->assertSame($expected, $panel->options());
	}

	public function testOptionsWithLockedUser(): void
	{
		$user = new UserForceLocked([
			'email' => 'test@infinri.com',
		]);

		$user->infinri()->impersonate('infinri');

		// without override
		$expected = [
			'access'         => false,
			'create'         => false,
			'changeEmail'    => false,
			'changeLanguage' => false,
			'changeName'     => false,
			'changePassword' => false,
			'changeRole'     => false,
			'delete'         => false,
			'list'           => false,
			'update'         => false,
		];

		$panel = new User($user);
		$this->assertSame($expected, $panel->options());

		// with override
		$expected = [
			'access'         => false,
			'create'         => false,
			'changeEmail'    => true,
			'changeLanguage' => false,
			'changeName'     => false,
			'changePassword' => false,
			'changeRole'     => false,
			'delete'         => false,
			'list'           => false,
			'update'         => false,
		];

		$this->assertSame($expected, $panel->options(['changeEmail']));
	}

	public function testPath(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$panel = new User($user);
		$this->assertTrue(Str::startsWith($panel->path(), 'users/'));
	}

	public function testPickerDataDefault(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$panel = new User($user);
		$data  = $panel->pickerData();

		$this->assertSame('test@infinri.com', $data['email']);
		$this->assertTrue(Str::startsWith($data['link'], '/users/'));
		$this->assertSame('test@infinri.com', $data['text']);
	}

	public function testProps(): void
	{
		$user = new ModelUser([
			'email'    => 'test@infinri.com',
			'language' => 'de'
		]);

		$panel = new User($user);
		$props = $panel->props();

		$this->assertArrayNotHasKey('model', $props);
		$this->assertArrayHasKey('avatar', $props);
		$this->assertArrayHasKey('email', $props);
		$this->assertArrayHasKey('language', $props);
		$this->assertArrayHasKey('name', $props);
		$this->assertArrayHasKey('role', $props);
		$this->assertArrayHasKey('username', $props);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('versions', $props);

		$this->assertNull($props['next']());
		$this->assertNull($props['prev']());
	}

	public function testPropsPrevNext(): void
	{
		$app = $this->app->clone([
			'users' => [
				['email' => 'a@infinri.com'],
				['email' => 'b@infinri.com'],
				['email' => 'c@infinri.com']
			]
		]);

		$app->impersonate('infinri');

		$props = (new User($app->user('a@infinri.com')))->props();
		$this->assertNull($props['prev']());
		$this->assertSame('b@infinri.com', $props['next']()['title']);

		$props = (new User($app->user('b@infinri.com')))->props();
		$this->assertSame('a@infinri.com', $props['prev']()['title']);
		$this->assertSame('c@infinri.com', $props['next']()['title']);

		$props = (new User($app->user('c@infinri.com')))->props();
		$this->assertSame('b@infinri.com', $props['prev']()['title']);
		$this->assertNull($props['next']());
	}

	public function testTranslation(): void
	{
		// existing
		$user = new ModelUser([
			'email'    => 'test@infinri.com',
			'language' => 'de'
		]);

		$panel = new User($user);
		$translations = $panel->translation();
		$this->assertSame('de', $translations->code());
		$this->assertSame('Deutsch', $translations->get('translation.name'));

		// non-existing
		$user = new ModelUser([
			'email'    => 'test@infinri.com',
			'language' => 'foo'
		]);

		$panel = new User($user);
		$translations = $panel->translation();
		$this->assertSame('foo', $translations->code());
		$this->assertNull($translations->get('translation.name'));
	}

	public function testPrevNext(): void
	{
		$app = $this->app->clone([
			'users' => [
				['email' => 'a@infinri.com'],
				['email' => 'b@infinri.com'],
				['email' => 'c@infinri.com']
			]
		]);

		$app->impersonate('infinri');

		$prevNext = (new User($app->user('a@infinri.com')))->prevNext();
		$this->assertNull($prevNext['prev']());
		$this->assertSame('b@infinri.com', $prevNext['next']()['title']);

		$prevNext = (new User($app->user('b@infinri.com')))->prevNext();
		$this->assertSame('a@infinri.com', $prevNext['prev']()['title']);
		$this->assertSame('c@infinri.com', $prevNext['next']()['title']);

		$prevNext = (new User($app->user('c@infinri.com')))->prevNext();
		$this->assertSame('b@infinri.com', $prevNext['prev']()['title']);
		$this->assertNull($prevNext['next']());
	}

	public function testPrevNextWithTab(): void
	{
		$app = $this->app->clone([
			'users' => [
				['id' => 'a', 'email' => 'a@infinri.com'],
				['id' => 'b', 'email' => 'b@infinri.com'],
				['id' => 'c', 'email' => 'c@infinri.com']
			]
		]);

		$app->impersonate('infinri');

		$_GET['tab'] = 'test';

		$prevNext = (new User($app->user('b@infinri.com')))->prevNext();
		$this->assertSame('/users/a?tab=test', $prevNext['prev']()['link']);
		$this->assertSame('/users/c?tab=test', $prevNext['next']()['link']);

		$_GET = [];
	}

	public function testPrevNextWithNonListableUsers(): void
	{
		// use uuid-based roles to avoid static permission cache collisions
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
					'id'    => 'a',
					'email' => 'a@infinri.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'id'    => 'b',
					'email' => 'b@infinri.com',
					'role'  => 'restricted-' . $uuid
				],
				[
					'id'    => 'c',
					'email' => 'c@infinri.com',
					'role'  => 'editor-' . $uuid
				]
			]
		]);

		// as a regular editor, non-listable user b is skipped in navigation
		$app->impersonate('a@infinri.com');

		$prevNext = (new User($app->user('a@infinri.com')))->prevNext();
		$this->assertNull($prevNext['prev']());
		$this->assertSame('c@infinri.com', $prevNext['next']()['title']);

		$prevNext = (new User($app->user('c@infinri.com')))->prevNext();
		$this->assertSame('a@infinri.com', $prevNext['prev']()['title']);
		$this->assertNull($prevNext['next']());

		// the infinri superuser sees all users including non-listable ones
		$app->impersonate('infinri');

		$prevNext = (new User($app->user('a@infinri.com')))->prevNext();
		$this->assertSame('b@infinri.com', $prevNext['next']()['title']);

		$prevNext = (new User($app->user('c@infinri.com')))->prevNext();
		$this->assertSame('b@infinri.com', $prevNext['prev']()['title']);
	}

	public function testView(): void
	{
		$user = new ModelUser([
			'email' => 'test@infinri.com',
		]);

		$panel = new User($user);
		$view = $panel->view();

		$this->assertArrayHasKey('props', $view);
		$this->assertSame('k-user-view', $view['component']);
		$this->assertSame('test@infinri.com', $view['title']);
		$this->assertSame('test@infinri.com', $view['breadcrumb'][0]['label']);
	}
}
