<?php

namespace Infinri\Cms;

use Infinri\Content\Field;
use Infinri\Content\MemoryStorage;
use Infinri\Content\PlainTextStorage;
use Infinri\Email\Email;
use Infinri\Exception\NotFoundException;
use Infinri\Filesystem\F;
use Infinri\Template\Template;
use Infinri\Toolkit\Obj;

class CustomEmailProvider extends Email
{
	public static string|null $apiKey = null;

	public function __construct(array $props = [], bool $debug = false)
	{
		parent::__construct($props, $debug);
	}

	public function send(bool $debug = false): bool
	{
		static::$apiKey = 'INFINRI';
		return true;
	}
}

class AppComponentsTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.AppComponents';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testCssPlugin(): void
	{
		$this->app->clone([
			'components' => [
				'css' => fn ($infinri, $url, $options) => '/test.css'
			]
		]);

		$expected = '<link href="/test.css" rel="stylesheet">';
		$this->assertSame($expected, css('something.css'));
	}

	public function testJsPlugin(): void
	{
		$this->app->clone([
			'components' => [
				'js' => fn ($infinri, $url, $options) => '/test.js'
			]
		]);

		$expected = '<script src="/test.js"></script>';
		$this->assertSame($expected, js('something.js'));
	}

	public function testInfinriTag(): void
	{
		$tag = $this->app->infinritag('link', 'https://infinri.com', ['text' => 'Infinri']);
		$expected = '<a href="https://infinri.com">Infinri</a>';

		$this->assertSame($expected, $tag);
	}

	public function testInfinriTags(): void
	{
		$tag = $this->app->infinritags('(link: https://infinri.com text: Infinri)');
		$expected = '<a href="https://infinri.com">Infinri</a>';

		$this->assertSame($expected, $tag);
	}

	public function testInfinritext(): void
	{
		$text     = 'Test';
		$expected = '<p>Test</p>';

		$this->assertSame($expected, $this->app->infinritext($text));
	}

	public function testInfinritextWithSafeMode(): void
	{
		$text     = '<h1>**Test**</h1>';
		$expected = '&lt;h1&gt;<strong>Test</strong>&lt;/h1&gt;';

		$this->assertSame($expected, $this->app->infinritext($text, [
			'markdown' => [
				'safe'   => true,
				'inline' => true
			]
		]));
	}

	public function testInfinritextInline(): void
	{
		$text     = 'Test';
		$expected = 'Test';

		$this->assertSame($expected, $this->app->infinritext($text, [
			'markdown' => [
				'inline' => true
			]
		], true));
	}

	public function testMarkdown(): void
	{
		$text     = 'Test';
		$expected = '<p>Test</p>';

		$this->assertSame($expected, $this->app->markdown($text));
	}

	public function testMarkdownInline(): void
	{
		$text     = 'Test';
		$expected = 'Test';

		$this->assertSame($expected, $this->app->markdown($text, ['inline' => true]));
	}

	public function testMarkdownWithSafeMode(): void
	{
		$text     = '<div>Test</div>';
		$expected = '<p>&lt;div&gt;Test&lt;/div&gt;</p>';

		$this->assertSame($expected, $this->app->markdown($text, ['safe' => true]));
	}

	public function testMarkdownCachedInstance(): void
	{
		$text     = '1st line
2nd line';
		$expected = '<p>1st line<br />
2nd line</p>';

		$this->assertSame($expected, $this->app->component('markdown')($this->app, $text, []));

		$expected = '<p>1st line
2nd line</p>';
		$this->assertSame($expected, $this->app->component('markdown')($this->app, $text, ['breaks' => false]));
	}

	public function testMarkdownPlugin(): void
	{
		$this->app = $this->app->clone([
			'components' => [
				'markdown' => function (App $infinri, string|null $text = null, array $options = []) {
					$result = Html::encode($text);

					if (($options['inline'] ?? false) === false) {
						$result = '<p>' . $result . '</p>';
					}

					return '<pre><code>' . $result . '</pre></code>';
				}
			]
		]);

		$text     = 'Test _case_';

		$expected = '<pre><code><p>Test _case_</p></pre></code>';
		$this->assertSame($expected, $this->app->markdown($text));

		$expected = '<pre><code>Test _case_</pre></code>';
		$this->assertSame($expected, $this->app->markdown($text, ['inline' => true]));
	}

	public function testSmartypants(): void
	{
		$text     = '"Test"';
		$expected = '“Test”';

		$this->assertSame($expected, $this->app->smartypants($text));
	}

	public function testSmartypantsDisabled(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'smartypants'   => false
			]
		]);

		$text     = '"Test"';
		$expected = '"Test"';

		$this->assertSame($expected, $this->app->smartypants($text));
	}

	public function testSmartypantsOptions(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages'   => true,
				'smartypants' => [
					'doublequote.open'  => '<',
					'doublequote.close' => '>'
				]
			]
		]);

		$text     = '"Test"';
		$expected = '<Test>';

		$this->assertSame($expected, $this->app->smartypants($text));
	}

	public function testSmartypantsMultiLang(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages'     => true,
				'smartypants'   => true
			],
			'languages' => [
				[
					'code'          => 'en',
					'name'          => 'English',
					'default'       => true,
					'locale'        => 'en_US',
					'url'           => '/',
					'smartypants'   => [
						'doublequote.open'  => '<',
						'doublequote.close' => '>'
					]
				],
				[
					'code'          => 'de',
					'name'          => 'Deutsch',
					'locale'        => 'de_DE',
					'url'           => '/de',
					'smartypants'   => [
						'doublequote.open'  => '<<',
						'doublequote.close' => '>>'
					]
				]
			]
		]);

		$text     = '"Test"';
		$expected = '<Test>';

		$this->assertSame($expected, $this->app->smartypants($text));
	}

	public function testSmartypantsDefaultOptionsOnMultiLang(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages'     => true,
				'smartypants'   => true
			],
			'languages' => [
				[
					'code'          => 'en',
					'name'          => 'English',
					'default'       => true,
					'locale'        => 'en_US',
					'url'           => '/'
				],
				[
					'code'          => 'de',
					'name'          => 'Deutsch',
					'locale'        => 'de_DE',
					'url'           => '/de'
				]
			]
		]);

		$text     = '"Test"';
		$expected = '“Test”';

		$this->assertSame($expected, $this->app->smartypants($text));
	}

	public function testSmartypantsCachedInstance(): void
	{
		$text     = '"Test"';
		$expected = '“Test”';

		$this->assertSame($expected, $this->app->component('smartypants')($this->app, $text, []));

		$expected = 'TestTest”';
		$this->assertSame($expected, $this->app->component('smartypants')($this->app, $text, ['doublequote.open' => 'Test']));
	}

	public function testSnippet(): void
	{
		$app = $this->app->clone([
			'roots' => [
				'snippets' => static::TMP
			],
			'snippets' => [
				'plugin' => static::TMP . '/plugin-snippet.php' // explicitly different filename
			]
		]);

		F::write(static::TMP . '/variable.php', '<?= $message;');
		F::write(static::TMP . '/item.php', '<?= $item->method();');
		F::write(static::TMP . '/test.php', 'test');
		F::write(static::TMP . '/fallback.php', 'fallback');
		F::write(static::TMP . '/plugin-snippet.php', 'plugin');

		// simple string
		$this->assertSame('test', $app->snippet('test'));

		// field
		$this->assertSame('test', $app->snippet(new Field(null, 'test', 'test')));

		// fallback
		$this->assertSame('fallback', $app->snippet(['does-not-exist', 'fallback']));

		// fallback from field
		$this->assertSame('fallback', $app->snippet(['does-not-exist', new Field(null, 'test', 'fallback')]));

		// from plugin
		$this->assertSame('plugin', $app->snippet('plugin'));

		// from plugin with field
		$this->assertSame('plugin', $app->snippet(new Field(null, 'test', 'plugin')));

		// fallback from plugin
		$this->assertSame('plugin', $app->snippet(['does-not-exist', 'plugin']));

		// fallback from plugin with field
		$this->assertSame('plugin', $app->snippet(['does-not-exist', new Field(null, 'test', 'plugin')]));

		// inject data
		$this->assertSame('test', $app->snippet('variable', ['message' => 'test']));

		// with a passed object that becomes $item
		$result = $app->snippet('item', new Obj(['method' => 'Hello world']));
		$this->assertSame('Hello world', $result);

		// with direct output
		$this->expectOutputString('test');
		$app->snippet('variable', ['message' => 'test'], false);
	}

	public function testStorage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->assertInstanceOf(PlainTextStorage::class, $this->app->storage($this->app->page('test')));
	}

	public function testStorageWithModifiedComponent(): void
	{
		$this->app = $this->app->clone([
			'components' => [
				'storage' => function (App $app, ModelWithContent $model) {
					return new MemoryStorage($model);
				}
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->assertInstanceOf(MemoryStorage::class, $this->app->storage($this->app->page('test')));
	}

	public function testTemplate(): void
	{
		$this->assertInstanceOf(Template::class, $this->app->template('default'));
	}

	public function testUrlPreservesExtensionOnMultilangSite(): void
	{
		$this->app->clone([
			'urls' => [
				'index' => 'https://infinri.com'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'url'     => '/'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'url'     => '/de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'articles']
				]
			]
		]);

		// extension is preserved for the default language
		$this->assertSame(
			'https://infinri.com/articles.xml',
			url('articles.xml')
		);

		// extension is preserved for a secondary language
		$this->assertSame(
			'https://infinri.com/de/articles.xml',
			url('articles.xml', 'de')
		);

		// works without extension as before
		$this->assertSame(
			'https://infinri.com/articles',
			url('articles')
		);

		// fragment is still appended correctly alongside the extension
		$this->assertSame(
			'https://infinri.com/articles.xml#section',
			url('articles.xml#section')
		);
	}

	public function testUrlPlugin(): void
	{
		$this->app->clone([
			'components' => [
				'url' => fn ($infinri, $path, $options) => 'test'
			]
		]);

		$this->assertSame('test', url('anything'));
	}

	public function testUrlPluginWithNativeComponent(): void
	{
		$this->app->clone([
			'components' => [
				'url' => function ($infinri, $path, $options) {
					if ($path === 'test') {
						return 'test-path';
					}

					return $infinri->nativeComponent('url')($infinri, $path, $options);
				}
			]
		]);

		$this->assertSame('test-path', url('test'));
		$this->assertSame('/any/page', url('any/page'));
	}

	public function testUrlInvalidUuid(): void
	{
		$this->app->clone([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The model could not be found for "page://invalid" uuid');

		url('page://invalid');
	}

	public function testEmail(): void
	{
		$app = $this->app->clone([
			'components' => [
				'email' => function ($infinri, $props, $debug) {
					return new CustomEmailProvider($props, $debug);
				}
			]
		]);

		$email = $app->email([
			'from' => 'no-reply@supercompany.com',
			'to' => 'someone@gmail.com',
			'subject' => 'Thank you for your contact request',
			'body' => 'We will never reply'
		], ['debug' => true]);

		$this->assertInstanceOf(CustomEmailProvider::class, $email);
		$this->assertTrue(property_exists($email, 'apiKey'));
		$this->assertSame('no-reply@supercompany.com', $email->from());
		$this->assertSame(['someone@gmail.com' => null], $email->to());
		$this->assertSame('Thank you for your contact request', $email->subject());
		$this->assertSame('We will never reply', $email->body()->text());

		$this->assertNull($email::$apiKey);
		$email->send();
		$this->assertSame('INFINRI', $email::$apiKey);
	}
}
