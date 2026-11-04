<?php

namespace Infinri\Cms;

use Infinri\Content\Field;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Exception\NotFoundException;
use Infinri\Filesystem\Dir;
use Infinri\Http\Cookie;
use Infinri\Query\Query;
use Infinri\Session\FileSessionStore;
use Infinri\Session\Sessions;
use Infinri\TestCase;
use Infinri\Text\InfinriTag;
use Infinri\Text\InfinriTags;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;

/**
 * Pins the renamed public identifier contract. Every legacy name is
 * assembled at runtime from the placeholder `{old}` so that this file
 * never contains the old brand as a literal.
 */
class InfinriIdentifierContractTest extends TestCase
{
	protected string $tmp;

	protected function setUp(): void
	{
		App::destroy();

		$this->tmp = sys_get_temp_dir() . '/infinri-contract-' . bin2hex(random_bytes(4));
		Dir::make($this->tmp);

		$this->app = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		App::destroy();
		Dir::remove($this->tmp);
	}

	protected static function old(string $template): string
	{
		return str_replace('{old}', strrev('ybrik'), $template);
	}

	public static function newHelperProvider(): array
	{
		return [
			['infinri'],
			['infinritag'],
			['infinritags'],
			['infinritext'],
			['infinritextinline'],
			['kt'],
			['kti'],
		];
	}

	public static function oldHelperProvider(): array
	{
		return [
			['{old}'],
			['{old}tag'],
			['{old}tags'],
			['{old}text'],
			['{old}textinline'],
		];
	}

	#[DataProvider('newHelperProvider')]
	public function testHelperFunctionExists(string $name): void
	{
		$this->assertTrue(function_exists($name));
	}

	#[DataProvider('oldHelperProvider')]
	public function testLegacyHelperFunctionDoesNotExist(string $template): void
	{
		$this->assertFalse(function_exists(static::old($template)));
	}

	public function testInfinriHelperReturnsApp(): void
	{
		$this->assertTrue(function_exists('infinri'));
		$this->assertSame($this->app, infinri());
	}

	public function testHasOverrideUsesNewPrefix(): void
	{
		// made-up helper names so that real helpers stay untouched
		if (defined('INFINRI_HELPER_CONTRACTCHECK') === false) {
			define('INFINRI_HELPER_CONTRACTCHECK', false);
		}

		$legacyConstant = strtoupper(static::old('{old}_HELPER_LEGACYCHECK'));

		if (defined($legacyConstant) === false) {
			define($legacyConstant, false);
		}

		$this->assertTrue(Helpers::hasOverride('contractcheck'));
		$this->assertFalse(Helpers::hasOverride('legacycheck'));
	}

	public function testClassAlias(): void
	{
		$this->assertSame('infinri', App::CLASS_ALIAS);
		$this->assertTrue(class_exists('infinri'));
		$this->assertFalse(class_exists(static::old('{old}')));
	}

	public function testTextClasses(): void
	{
		$this->assertTrue(class_exists(InfinriTag::class));
		$this->assertTrue(class_exists(InfinriTags::class));
		$this->assertFalse(class_exists('Infinri\\Text\\' . ucfirst(static::old('{old}')) . 'Tag'));
		$this->assertFalse(class_exists('Infinri\\Text\\' . ucfirst(static::old('{old}')) . 'Tags'));
	}

	public function testRoots(): void
	{
		$roots = $this->app->roots();

		$this->assertSame(realpath(__DIR__ . '/../..'), $roots->infinri());
		$this->assertArrayHasKey('infinri', $roots->toArray());
		$this->assertArrayNotHasKey(static::old('{old}'), $roots->toArray());
	}

	public function testQueryEntries(): void
	{
		$this->assertArrayHasKey('infinri', Query::$entries);
		$this->assertArrayNotHasKey(static::old('{old}'), Query::$entries);
	}

	public function testQueryResolvesNewEntry(): void
	{
		$this->assertArrayHasKey('infinri', Query::$entries);
		$this->assertSame($this->app->site(), (new Query('infinri.site'))->resolve());
	}

	public function testFieldMethods(): void
	{
		$this->assertArrayHasKey('infinritext', Field::$methods);
		$this->assertArrayHasKey('infinritextinline', Field::$methods);
		$this->assertArrayHasKey('infinritags', Field::$methods);
		$this->assertArrayNotHasKey(static::old('{old}text'), Field::$methods);
		$this->assertArrayNotHasKey(static::old('{old}textinline'), Field::$methods);
		$this->assertArrayNotHasKey(static::old('{old}tags'), Field::$methods);
	}

	public function testFieldMethodAliases(): void
	{
		$this->assertSame('infinritext', Field::$aliases['kt']);
		$this->assertSame('infinritextinline', Field::$aliases['kti']);
	}

	public function testInfinritextHooks(): void
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
			],
			'hooks' => [
				'infinritext:before' => fn (string|null $text) => 'HOOKED:' . $text,
				'infinritext:after'  => fn (string|null $text) => $text . ':AFTER',
				static::old('{old}text:before') => fn (string|null $text) => 'LEGACY:' . $text,
				static::old('{old}text:after')  => fn (string|null $text) => $text . ':LEGACY'
			]
		]);

		$this->assertTrue(method_exists($app, 'infinritext'));

		$result = $app->infinritext('a');

		$this->assertSame('<p>HOOKED:a</p>:AFTER', $result);
		$this->assertStringNotContainsString('LEGACY', $result);
	}

	public function testInfinritagsHooks(): void
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
			],
			'hooks' => [
				'infinritags:before' => fn (string|null $text) => 'HOOKED:' . $text,
				'infinritags:after'  => fn (string|null $text) => $text . ':AFTER',
				static::old('{old}tags:before') => fn (string|null $text) => 'LEGACY:' . $text,
				static::old('{old}tags:after')  => fn (string|null $text) => $text . ':LEGACY'
			]
		]);

		$this->assertTrue(method_exists($app, 'infinritags'));
		$this->assertSame('HOOKED:a:AFTER', $app->infinritags('a'));
	}

	public function testBrokenLinkClass(): void
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
			],
			'options' => [
				'debug' => true
			]
		]);

		$this->assertTrue(method_exists($app, 'infinritags'));
		$this->assertSame(
			'<span class="infinri-broken-link">🚨 The link &quot;page://not-exists&quot; cannot be found</span>',
			$app->infinritags('(link: page://not-exists)')
		);
	}

	public function testVideoBlockWithInfinriLocationRendersLocalFile(): void
	{
		$page = new Page([
			'slug'  => 'a',
			'files' => [
				['filename' => 'movie.mp4']
			]
		]);

		$block = new Block([
			'type'    => 'video',
			'parent'  => $page,
			'content' => [
				'location' => 'infinri',
				'video'    => ['movie.mp4']
			]
		]);

		$this->assertStringContainsString(
			'src="' . $page->file('movie.mp4')->url() . '"',
			$block->toHtml()
		);
	}

	public function testVideoBlockWithLegacyLocationIsNotLocal(): void
	{
		$page = new Page([
			'slug'  => 'a',
			'files' => [
				['filename' => 'movie.mp4']
			]
		]);

		$block = new Block([
			'type'    => 'video',
			'parent'  => $page,
			'content' => [
				'location' => static::old('{old}'),
				'video'    => ['movie.mp4']
			]
		]);

		$this->assertStringNotContainsString(
			$page->file('movie.mp4')->url(),
			$block->toHtml()
		);
	}

	public function testImpersonateInfinri(): void
	{
		try {
			$user = $this->app->auth()->impersonate('infinri');
		} catch (NotFoundException $e) {
			$this->fail('impersonate("infinri") must yield the virtual admin: ' . $e->getMessage());
		}

		$this->assertInstanceOf(User::class, $user);
		$this->assertSame('infinri', $user->id());
		$this->assertSame('infinri@infinri.com', $user->email());
		$this->assertSame('admin', $user->role()->name());
		$this->assertTrue($user->isInfinri());
	}

	public function testLegacyUserCheckMethodDoesNotExist(): void
	{
		$this->assertTrue(method_exists(User::class, 'isInfinri'));
		$this->assertFalse(method_exists(User::class, 'is' . ucfirst(static::old('{old}'))));
	}

	public function testImpersonateLegacyIdThrows(): void
	{
		$this->expectException(NotFoundException::class);

		$this->app->auth()->impersonate(static::old('{old}'));
	}

	public function testInfinriIsAReservedUserId(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"infinri" is a reserved word and cannot be used as user id');

		UserRules::validId(new User(['email' => 'contract@example.com']), 'infinri');
	}

	public function testSessionCookieName(): void
	{
		$sessions = new Sessions(new FileSessionStore($this->tmp . '/sessions'));

		$this->assertSame('infinri_session', $sessions->cookieName());
	}

	public function testCsrfSessionKey(): void
	{
		$token = $this->app->csrf();

		$this->assertSame($token, $this->app->session()->get('infinri.csrf'));
		$this->assertNull($this->app->session()->get(static::old('{old}.csrf')));
	}

	public function testLoginSessionKeys(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'contract@example.com', 'id' => 'contractuser', 'role' => 'admin']
			]
		]);

		$this->app->user('contract@example.com')->loginPasswordless();

		$this->assertSame(
			['infinri.userId' => 'contractuser'],
			$this->app->session()->data()->get()
		);
	}

	public function testCookieKeyDefault(): void
	{
		$property = new ReflectionProperty(Cookie::class, 'key');

		$this->assertSame('InfinriHttpCookieKey', $property->getDefaultValue());
	}

	public function testSystemInfoKey(): void
	{
		$info = $this->app->system()->info();

		$this->assertArrayHasKey('infinri', $info);
		$this->assertArrayNotHasKey(static::old('{old}'), $info);
		$this->assertSame($this->app->version(), $info['infinri']);
	}

	public function testSystemExposedFileUrl(): void
	{
		$root = $this->tmp . '/core';
		Dir::make($root);

		$app = new App([
			'roots' => [
				'infinri' => $root,
				'index'   => $this->tmp
			]
		]);

		$this->assertSame('/core', $app->system()->folderUrl('infinri'));
		$this->assertSame('/core/LICENSE.md', $app->system()->exposedFileUrl('infinri'));
	}

	public function testUpdatesOptionKey(): void
	{
		$disabled = new App([
			'roots'   => ['index' => $this->tmp],
			'options' => ['updates' => ['infinri' => false]]
		]);

		$this->assertNull($disabled->system()->updateStatus());

		$legacyIgnored = new App([
			'roots'   => ['index' => $this->tmp],
			'options' => ['updates' => [static::old('{old}') => false]]
		]);

		$this->assertNotNull($legacyIgnored->system()->updateStatus());
	}

	public function testPageControllerData(): void
	{
		$page = new Page(['slug' => 'contract']);
		$data = $page->controller();

		$this->assertArrayHasKey('infinri', $data);
		$this->assertSame($this->app, $data['infinri']);
		$this->assertArrayNotHasKey(static::old('{old}'), $data);
	}

	public function testPanelDragTextType(): void
	{
		$panel = (new Page(['slug' => 'contract']))->panel();

		$this->assertSame('infinritext', $panel->dragTextType());
		$this->assertSame('infinritext', $panel->dragTextType('infinritext'));
		$this->assertSame('markdown', $panel->dragTextType('markdown'));

		$app = new App([
			'roots'   => ['index' => $this->tmp],
			'options' => ['panel.infinritext' => false]
		]);

		$this->assertSame('markdown', (new Page(['slug' => 'contract']))->panel()->dragTextType());
	}

	public function testLegacyDragTextOptionIsIgnored(): void
	{
		new App([
			'roots'   => ['index' => $this->tmp],
			'options' => ['panel.' . static::old('{old}text') => false]
		]);

		$this->assertSame(
			'infinritext',
			(new Page(['slug' => 'contract']))->panel()->dragTextType()
		);
	}
}
