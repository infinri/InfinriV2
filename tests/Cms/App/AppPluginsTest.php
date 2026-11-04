<?php

namespace Infinri\Cms;

use Infinri\Cache\FileCache;
use Infinri\Cms\Auth\Challenge;
use Infinri\Content\Field;
use Infinri\Filesystem\Dir;
use Infinri\Filesystem\F;
use Infinri\Filesystem\Mime;
use Infinri\Form\Field as FormField;
use Infinri\Image\Image;
use Infinri\Plugin\Plugin;
use Infinri\Toolkit\Collection;
use Infinri\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

class DummyAuthChallenge extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	public static function create(User $user, array $options): string|null
	{
		return 'test';
	}

	public static function verify(User $user, string $code): bool
	{
		return $code === 'test-verify';
	}
}

class DummyCache extends FileCache
{
}

class DummyFile extends File
{
}

class DummyPage extends Page
{
}

class DummyUser extends User
{
}

class DummyFilePreview
{
}

#[CoversClass(AppPlugins::class)]
class AppPluginsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = INFINRI_TMP_DIR . '/Cms.AppPlugins';

	// used for testPluginLoader()
	public static bool $calledPluginsLoadedHook = false;

	public function testApi(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'api' => [
				'routes' => [
					[
						'pattern' => 'awesome',
						'action'  => fn () => 'nice'
					]
				],
				'authentication' => fn () => true
			]
		]);

		$infinri->impersonate('infinri');
		$this->assertSame('nice', $infinri->call('api/awesome'));
	}

	public function testApiRoutePlugins(): void
	{
		App::plugin('test/a', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'a',
						'action'  => fn () => 'a'
					]
				]
			]
		]);

		App::plugin('test/b', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'b',
						'action'  => fn () => 'b'
					]
				]
			]
		]);

		App::plugin('test/c', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'c',
						'action'  => fn () => 'c'
					]
				]
			]
		]);

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'api' => [
				'authentication' => fn () => true
			],
		]);

		$app->impersonate('infinri');

		$this->assertSame('a', $app->api()->call('a'));
		$this->assertSame('b', $app->api()->call('b'));
		$this->assertSame('c', $app->api()->call('c'));
	}

	public function testApiRouteCallbackPlugins(): void
	{
		App::plugin('test/a', [
			'api' => [
				'routes' => fn ($infinri) => [
					[
						'pattern' => 'a',
						'action'  => fn () => $infinri->root('index')
					]
				]
			]
		]);

		App::plugin('test/b', [
			'api' => [
				'routes' => fn ($infinri) => [
					[
						'pattern' => 'b',
						'action'  => fn () => 'b'
					]
				]
			]
		]);

		App::plugin('test/c', [
			'api' => [
				'routes' => [
					[
						'pattern' => 'c',
						'action'  => fn () => 'c'
					]
				]
			]
		]);

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'api' => [
				'authentication' => fn () => true
			],
		]);

		$app->impersonate('infinri');

		$this->assertSame('/dev/null', $app->api()->call('a'));
		$this->assertSame('b', $app->api()->call('b'));
		$this->assertSame('c', $app->api()->call('c'));
	}

	public function testApiRouteCallbackPluginWithOptionAccess(): void
	{
		App::plugin('your/plugin', [
			'options' => [
				'test' => 'Test'
			],
			'api' => [
				'routes' => fn ($infinri) => [
					[
						'pattern' => 'test',
						'action'  => fn () => $infinri->option('your.plugin.test')
					]
				]
			]
		]);

		$app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		$app->impersonate('infinri');
		$this->assertSame('Test', $app->api()->call('test'));
	}

	public function testAuthChallenge(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => static::TMP
			],
			'authChallenges' => [
				'dummy' => 'Infinri\Cms\DummyAuthChallenge'
			],
			'options' => [
				'auth.challenges' => ['dummy']
			],
			'users' => [
				[
					'email' => 'homer@simpsons.com'
				]
			]
		]);
		$auth    = $infinri->auth();
		$session = $infinri->session();

		$status = $auth->createChallenge('homer@simpsons.com');
		$this->assertSame([
			'challenge' => 'dummy',
			'email'     => 'homer@simpsons.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('dummy', $status->challenge(false));
		$this->assertSame('homer@simpsons.com', $session->get('infinri.challenge.email'));
		$this->assertSame('login', $session->get('infinri.challenge.mode'));
		$this->assertSame('dummy', $session->get('infinri.challenge.type'));
		$this->assertTrue(password_verify('test', $session->get('infinri.challenge.code')));
		$this->assertSame(MockTime::$time + 600, $session->get('infinri.challenge.timeout'));

		$this->assertSame(
			$infinri->user('homer@simpsons.com'),
			$auth->verifyChallenge('test-verify')
		);

		$infinri->session()->destroy();
	}

	public function testBlueprint(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/test' => $file = 'test.yml'
			]
		]);

		$this->assertSame($file, $infinri->extension('blueprints', 'pages/test'));
	}

	public function testCacheType(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => static::TMP
			],
			'cacheTypes' => [
				'file' => DummyCache::class
			],
			'options' => [
				'cache' => [
					'pages' => true
				]
			]
		]);

		$this->assertInstanceOf(DummyCache::class, $infinri->cache('pages'));
	}

	public function testCollection(): void
	{
		$pages = new Pages([
			$page = new Page(['slug' => 'a', 'num' => 1])
		]);

		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'collections' => [
				'test' => fn () => $pages
			],
		]);

		$this->assertIsPage($page, $infinri->collection('test')->first());
	}

	public function testCollectionFilters(): void
	{
		// fetch all previous filters
		$prevFilters = Collection::$filters;

		Collection::$filters = [];

		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'collectionFilters' => [
				'**' => $filter = [
					'validator' => fn ($value, $test) => $value === 'foo'
				]
			]
		]);

		$this->assertSame(Collection::$filters['**'], $filter);

		// restore previous filters
		Collection::$filters = $prevFilters;
	}

	public function testCommands(): void
	{
		$pages = new Pages([]);
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'commands' => [
				'test' => $command = [
					'command' => function () {
					}
				]
			],
		]);

		$this->assertSame($command, $infinri->extension('commands', 'test'));
	}

	public function testController(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'controllers' => [
				'test' => fn () => ['foo' => 'bar']
			]
		]);

		$this->assertSame(['foo' => 'bar'], $infinri->controller('test'));
	}

	public function testFieldMethod(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'fieldMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->customField()->test());

		// reset methods
		Field::$methods = [];
	}

	public function testField(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'fields' => [
				'dummy' => static::FIXTURES . '/fields/DummyField.php'
			]
		]);

		$page  = new Page(['slug' => 'test']);
		$field = new FormField('dummy', [
			'name'  => 'dummy',
			'peter' => 'shaw',
			'model' => $page
		]);

		$this->assertInstanceOf(FormField::class, $field);
		$this->assertSame('simpson', $field->homer());
		$this->assertSame('shaw', $field->peter());
	}

	public function testFilePreviews(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'filePreviews' => [
				DummyFilePreview::class
			]
		]);

		$this->assertCount(5, $app->extensions('filePreviews'));
	}

	public function testInfinriTag(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'tags' => [
				'test' => [
					'html' => fn () => 'test'
				],
				'FoO' => [
					'html' => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $infinri->infinritags('(test: foo)'));
		$this->assertSame('test', $infinri->infinritags('(TEST: foo)'));

		$this->assertSame('test', $infinri->infinritags('(foo: bar)'));
		$this->assertSame('test', $infinri->infinritags('(FOO: bar)'));
	}

	public function testPageMethod(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'pageMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->test());

		// reset methods
		Page::$methods = [];
	}

	public function testPagesMethod(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'pagesMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$pages = new Pages([]);
		$this->assertSame('test', $pages->test());

		// reset methods
		Pages::$methods = [];
	}

	public function testPageModel(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'pageModels' => [
				'dummy' => DummyPage::class
			]
		]);

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(DummyPage::class, $page);
	}

	public function testPageModelFromFolder(): void
	{
		$infinri = new App([
			'roots' => [
				'index'  => '/dev/null',
				'models' => static::FIXTURES . '/models'
			]
		]);

		$page = Page::factory([
			'slug' => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf('TestPage', $page);
	}

	public function testPermission(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'permissions' => [
				'test-category' => [
					'test-action' => true,
					'another'     => false
				]
			]
		]);

		$permissions = new Permissions([]);
		$this->assertTrue($permissions->for('test-category', 'test-action'));
		$this->assertFalse($permissions->for('test-category', 'another'));

		// reset actions
		Permissions::$extendedActions = [];
	}

	public function testPermissionPlugin(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$plugin = new Plugin('infinri/manual', [
			'permissions' => [
				'test-action' => true,
				'another'     => false
			]
		]);

		$infinri->extend($plugin->extends(), $plugin);

		$permissions = new Permissions([]);
		$this->assertTrue($permissions->for('infinri.manual', 'test-action'));
		$this->assertFalse($permissions->for('infinri.manual', 'another'));

		// reset actions
		Permissions::$extendedActions = [];
	}

	public function testOption(): void
	{
		// simple
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'testOption' => 'testValue'
			]
		]);

		$this->assertSame('testValue', $infinri->option('testOption'));
	}

	public function testExtensionsFromFolders(): void
	{
		Page::$models = [];
		Dir::copy(static::FIXTURES . '/AppPluginsTest', static::TMP);

		$infinri = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$expected = [
			'regular' => 'regularPage',
			'with.dot' => 'withdotPage',
			'with-dash' => 'withdashPage',
			'with_underscore' => 'withunderscorePage'
		];

		$this->assertEquals($expected, Page::$models); // cannot use strict assertion (filesystem sorting)
	}

	public function testExtensionsFromOptions(): void
	{
		$calledRoute = false;
		$calledHook  = false;

		$infinri = new App([
			'options' => [
				'routes' => [
					[
						'pattern' => 'test',
						'action'  => function () use (&$calledRoute) {
							$calledRoute = true;
						}
					]
				],
				'hooks' => [
					'type.action:state' => function () use (&$calledHook) {
						$calledHook = true;
					}
				]
			]
		]);

		$infinri->call('test');
		$infinri->trigger('type.action:state');
		$this->assertTrue($calledRoute);
		$this->assertTrue($calledHook);
	}

	public function testPluginWithoutRootIgnoresExtendsRoot(): void
	{
		$plugin = App::plugin('test/rootless', [
			'root' => '/some/other/root'
		]);

		$this->assertSame(__DIR__, $plugin->root());
	}

	public function testPluginOptions(): void
	{
		App::plugin('test/plugin', [
			'options' => [
				'foo' => 'bar'
			]
		]);

		// simple
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'test.plugin' => [
					'foo' => 'another-bar'
				]
			]
		]);

		$this->assertSame('another-bar', $infinri->option('test.plugin.foo'));
		$this->assertSame(['foo' => 'another-bar'], $infinri->option('test.plugin'));
	}

	public function testPluginOptionsWithNonAssociativeArray(): void
	{
		// non-associative
		App::plugin('test/plugin', [
			'options' => [
				'foo' => ['one', 'two']
			]
		]);

		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'test.plugin' => [
					'foo' => ['three']
				]
			]
		]);

		$this->assertSame(['three'], $infinri->option('test.plugin.foo'));
	}

	public function testPluginOptionsWithAssociativeArray(): void
	{
		// associative
		App::plugin('test/plugin', [
			'options' => [
				'foo' => [
					'a' => 'A',
					'b' => 'B'
				]
			]
		]);

		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'test.plugin' => [
					'foo' => [
						'a' => 'Custom A'
					]
				]
			]
		]);

		$this->assertSame(['a' => 'Custom A', 'b' => 'B'], $infinri->option('test.plugin.foo'));
	}

	public function testRoutes(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'routes' => [
				[
					'pattern' => 'test',
					'action'  => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $infinri->call('test'));
	}

	public function testRoutesCallback(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'routes' => fn () => [
				[
					'pattern' => 'test',
					'action'  => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $infinri->call('test'));
	}

	public function testSnippet(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'snippets' => [
				'header' => $file = 'header.php'
			]
		]);

		$this->assertSame($file, $infinri->extension('snippets', 'header'));
	}

	public function testTemplate(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'templates' => [
				'project' => $file = 'project.php'
			]
		]);

		$this->assertSame($file, $infinri->extension('templates', 'project'));
	}

	public function testTranslation(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'translations' => [
				'en' => [
					'test' => 'English Test'
				],
				'de' => [
					'test' => 'Deutscher Test'
				]
			]
		]);

		I18n::$locale = 'en';

		$this->assertSame('English Test', I18n::translate('test'));

		I18n::$locale = 'de';

		$this->assertSame('Deutscher Test', I18n::translate('test'));
	}

	public function testTranslationsInPlugin(): void
	{
		App::plugin('test/test', [
			'translations' => [
				'en' => [
					'test' => 'English Test'
				],
				'de' => [
					'test' => 'Deutscher Test'
				]
			]
		]);

		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		I18n::$locale = 'en';

		$this->assertSame('English Test', I18n::translate('test'));

		I18n::$locale = 'de';

		$this->assertSame('Deutscher Test', I18n::translate('test'));
	}

	public function testTranslationsWithWarmCache(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		// warm all translation caches before the strings are registered
		I18n::$locale = 'de';
		I18n::translate('test');
		$infinri->translations();
		$infinri->coreI18nStrings('de');

		$infinri->extend([
			'translations' => [
				'de' => [
					'test' => 'Deutscher Test'
				]
			]
		]);

		$this->assertSame('Deutscher Test', I18n::translate('test'));
		$this->assertSame('Deutscher Test', $infinri->translation('de')->get('test'));
		$this->assertSame('Deutscher Test', $infinri->coreI18nStrings('de')['test']);
	}

	public function testUserMethod(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'userMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$user = new User([
			'email' => 'test@infinri.com',
			'name'  => 'Test User'
		]);
		$this->assertSame('test', $user->test());

		// reset methods
		User::$methods = [];
	}

	public function testUserModel(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'userModels' => [
				'dummy' => DummyUser::class
			]
		]);

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(DummyUser::class, $user);
	}

	public function testUsersMethod(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'usersMethods' => [
				'test' => fn () => 'test'
			]
		]);

		$users = new Users([]);
		$this->assertSame('test', $users->test());

		// reset methods
		Users::$methods = [];
	}

	public function testPluginLoader(): void
	{
		$phpUnit  = $this;
		$executed = 0;

		Dir::copy(static::FIXTURES . '/AppPluginsTest', $tmp = static::TMP);

		$infinri = new App([
			'roots' => [
				'index'   => static::TMP,
				'plugins' => static::TMP . '/site/plugins-loader'
			],
			'hooks' => [
				'system.loadPlugins:after' => function () use ($phpUnit, &$executed, $tmp) {
					if (count($this->plugins()) === 2) {
						$phpUnit->assertSame([
							'infinri/manual1' => new Plugin('infinri/manual1', []),
							'infinri/manual2' => new Plugin('infinri/manual2', [])
						], $this->plugins());
					} else {
						// cannot use strict assertion (test for object contents)
						$phpUnit->assertEquals([
							'infinri/test1' => new Plugin('infinri/test1', [
								'hooks' => [
									'system.loadPlugins:after' => function () {
										// just a dummy closure to compare against
									}
								]
							], root: $tmp . '/site/plugins-loader/test1')
						], $this->plugins());
					}

					$executed++;
				}
			]
		]);

		// the hook defined inside the test1 plugin should also have been called
		$this->assertTrue(static::$calledPluginsLoadedHook);

		// try loading again (which should *not* trigger the hooks again)
		$infinri->plugins();

		// overwrite plugins with a custom array
		$expected = [
			'infinri/manual1' => new Plugin('infinri/manual1', []),
			'infinri/manual2' => new Plugin('infinri/manual2', [])
		];
		$this->assertSame($expected, $infinri->plugins($expected));

		// hook should have been called only once after the first initialization
		$this->assertSame(1, $executed);
	}

	public function testPluginLoaderAnonymous(): void
	{
		Dir::copy(static::FIXTURES . '/AppPluginsTest', static::TMP);

		$infinri = new App([
			'roots' => [
				'index'   => static::TMP,
				'plugins' => $dir = static::TMP . '/site/plugins-loader-anonymous'
			]
		]);

		$plugins = $infinri->plugins();
		$this->assertCount(1, $plugins);

		$plugin = array_pop($plugins);
		$this->assertSame('plugins/test5', $plugin->name());
		$this->assertSame($dir . '/test5', $plugin->root());
	}

	public function testThirdPartyExtensions(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'tags' => [
				'test' => $testTag = function () {
				},
			],
			'thirdParty' => [
				'blocks' => [
					'test' => $testBlock = function () {
					}
				]
			]
		]);

		$this->assertSame($testTag, $infinri->extensions('tags')['test']);
		$this->assertSame($testBlock, $infinri->extensions('thirdParty')['blocks']['test']);
	}

	public function testNativeComponents(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'components' => [
				'url' => function ($infinri, $path) {
					return 'https://rewritten.infinri.com/' . $path;
				},
			]
		]);

		$this->assertSame('https://rewritten.infinri.com/test', $infinri->component('url')($infinri, 'test'));
		$this->assertSame('https://infinri.com/test', $infinri->nativeComponent('url')($infinri, 'test'));
	}

	public function testAreas(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'areas' => [
				'todos' => fn () => []
			]
		]);

		$areas = $infinri->extensions('areas');

		$this->assertCount(1, $areas);
		$this->assertArrayHasKey('todos', $areas);
		$this->assertInstanceOf('Closure', $areas['todos'][0]);
	}

	public function testFileTypes(): void
	{
		$infinri = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'fileTypes' => [
				'm4p' => [
					'mime' => 'video/m4p',
					'type' => 'video',
				],
				'heif' => [
					'mime' => ['image/heic', 'image/heif'],
					'type' => 'image',
					'resizable' => true,
					'viewable' => true,
				],
				'test' => [
					'extension' => 'kql',
					'type' => 'code'
				],
				'midi' => [
					'mime' => 'audio/x-midi'
				],
				'ttf' => [
					'type' => 'font'
				]
			]
		]);

		$fileTypes = $infinri->extensions('fileTypes');
		$this->assertSame($fileTypes['type'], F::$types);
		$this->assertSame($fileTypes['mime'], Mime::$types);
		$this->assertSame($fileTypes['resizable'], Image::$resizableTypes);
		$this->assertSame($fileTypes['viewable'], Image::$viewableTypes);

		$this->assertContains('m4p', F::$types['video']);
		$this->assertArrayHasKey('m4p', Mime::$types);
		$this->assertSame('video/m4p', Mime::$types['m4p']);
		$this->assertNotContains('m4p', Image::$resizableTypes);
		$this->assertNotContains('m4p', Image::$viewableTypes);

		$this->assertContains('heif', F::$types['image']);
		$this->assertArrayHasKey('heif', Mime::$types);
		$this->assertSame(['image/heif', 'image/heic'], Mime::$types['heif']);
		$this->assertContains('heif', Image::$resizableTypes);
		$this->assertContains('heif', Image::$viewableTypes);

		$this->assertContains('kql', F::$types['code']);
		$this->assertNotContains('kql', Image::$resizableTypes);
		$this->assertNotContains('kql', Image::$viewableTypes);

		$this->assertArrayHasKey('midi', Mime::$types);
		$this->assertSame(['audio/midi', 'audio/x-midi'], Mime::$types['midi']);
		$this->assertNotContains('midi', Image::$resizableTypes);
		$this->assertNotContains('midi', Image::$viewableTypes);

		$this->assertArrayHasKey('font', F::$types);
		$this->assertContains('ttf', F::$types['font']);
		$this->assertNotContains('ttf', Image::$resizableTypes);
		$this->assertNotContains('ttf', Image::$viewableTypes);
	}
}
