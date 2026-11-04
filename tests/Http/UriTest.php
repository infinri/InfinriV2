<?php

namespace Infinri\Http;

use Infinri\Cms\App;
use Infinri\Exception\InvalidArgumentException;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TypeError;

#[CoversClass(Uri::class)]
class UriTest extends TestCase
{
	protected static string $example1 = 'https://infinri.com';
	protected static string $example2 = 'https://testuser:weakpassword@infinri.com:3000/docs/getting-started/with:infinri/?q=awesome#top';

	protected function setUp(): void
	{
		App::destroy();
		Uri::$current = null;
	}

	public function testClone(): void
	{
		$uri = new Uri([
			'host' => 'infinri.com',
			'path' => 'test'
		]);

		$clone = $uri->clone([
			'path'  => 'yay',
			'query' => ['foo' => 'bar']
		]);

		$this->assertSame('http://infinri.com/test', $uri->toString());
		$this->assertSame('http://infinri.com/yay?foo=bar', $clone->toString());
	}

	public function testCurrent(): void
	{
		new App([
			'cli' => false,
			'options' => [
				'url' => 'https://infinri.com'
			],
			'server' => [
				'REQUEST_URI' => '/docs/reference'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('https://infinri.com/docs/reference', $uri->toString());
	}

	public function testCurrentInCli(): void
	{
		$uri = Uri::current();
		$this->assertSame('/', $uri->toString());
	}

	public function testCurrentWithCustomObject(): void
	{
		Uri::$current = $uri = new Uri('/');

		$this->assertSame($uri, Uri::current());
	}

	public function testCurrentWithRequestUri(): void
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => '/a/b'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/a/b', $uri->toString());
	}

	public function testCurrentWithHostAndPathInRequestUri(): void
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => 'http://ktest.loc/a/b'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/a/b', $uri->toString());
	}

	public function testCurrentWithHostAndSchemeInRequestUri(): void
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => 'http://ktest.loc/'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/', $uri->toString());
	}

	public function testCurrentWithHostInRequestUri(): void
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => 'http://ktest.loc/a/b/ktest.loc'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/a/b/ktest.loc', $uri->toString());
	}

	public function testFragment(): void
	{
		$uri = new Uri('https://infinri.com#top');
		$this->assertSame('top', $uri->fragment());

		$uri = new Uri('https://infinri.com');
		$this->assertNull($uri->fragment());
	}

	public function testValidScheme(): void
	{
		$url = new Uri();

		$url->setScheme('http');
		$this->assertSame('http', $url->scheme());

		$url->setScheme('https');
		$this->assertSame('https', $url->scheme());
	}

	public function testIdn(): void
	{
		$url = new Uri('https://xn--bcher-kva.ch');
		$this->assertSame('xn--bcher-kva.ch', $url->host());
		$this->assertSame('bücher.ch', $url->idn()->host());
	}

	public function testIdnWithMalformedPunycode(): void
	{
		// the host must not be dropped when the conversion fails
		$url = new Uri('https://xn--invalid--.example/a');
		$this->assertSame('xn--invalid--.example', $url->idn()->host());
		$this->assertSame('https://xn--invalid--.example/a', $url->toString());
	}

	public function testIndex(): void
	{
		new App([
			'cli' => false,
			'options' => [
				'url' => 'https://infinri.com'
			]
		]);

		$uri = Uri::index();
		$this->assertSame('https://infinri.com', $uri->toString());
	}

	public function testIndexInCli(): void
	{
		$uri = Uri::index();
		$this->assertSame('/', $uri->toString());
	}

	public function testInherit(): void
	{
		$base = new Uri('https://infinri.com');

		$uri = $base->inherit('https://foo.com/this/is/a/path');
		$this->assertSame('https://infinri.com', $uri->toString());

		$uri = $base->inherit('https://foo.com/fox:bax?foo=bar#anchor');
		$this->assertSame('https://infinri.com/fox:bax?foo=bar#anchor', $uri->toString());

		$base = new Uri('https://infinri.com?one=two');
		$uri  = $base->inherit('https://foo.com/?foo=bar');
		$this->assertSame('https://infinri.com?one=two&foo=bar', $uri->toString());
	}

	public function testInvalidScheme(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid URL scheme: abc');

		$url = new Uri();
		$url->setScheme('abc');
	}

	public function testValidHost(): void
	{
		$url = new Uri();

		$url->setHost('infinri.com');
		$this->assertSame('infinri.com', $url->host());
	}

	public function testMissingHost(): void
	{
		$url = new Uri(['host' => false]);
		$this->assertSame('', $url->host());
	}

	public function testIsAbsolute(): void
	{
		$url = new Uri(['host' => 'localhost']);
		$this->assertTrue($url->isAbsolute());
	}

	public function testIsNotAbsolute(): void
	{
		$url = new Uri();
		$this->assertFalse($url->isAbsolute());
	}

	public function testValidPort(): void
	{
		$url = new Uri(['port' => 1234]);
		$this->assertSame(1234, $url->port());

		$url = new Uri(['port' => null]);
		$this->assertNull($url->port());
	}

	public function testZeroPort(): void
	{
		$url = new Uri(['port' => 0]);
		$this->assertNull($url->port());
	}

	public function testInvalidPortFormat1(): void
	{
		$this->expectException(TypeError::class);

		new Uri(['port' => 'a']);
	}

	public function testInvalidPortFormat2(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid port format: 12010210210');

		$url = new Uri(['port' => 12010210210]);
	}

	public function testValidUsername(): void
	{
		$url = new Uri(['username' => 'testuser']);
		$this->assertSame('testuser', $url->username());

		$url = new Uri(['username' => null]);
		$this->assertNull($url->username());
	}

	public function testValidPassword(): void
	{
		$url = new Uri(['password' => 'weakpassword']);
		$this->assertSame('weakpassword', $url->password());

		$url = new Uri(['password' => null]);
		$this->assertNull($url->password());
	}

	public function testValidPath(): void
	{
		$url = new Uri(['path' => '/a/b/c']);
		$this->assertSame('a/b/c', $url->path()->toString());

		$url = new Uri(['path' => '/a/b/c/']);
		$this->assertSame('a/b/c', $url->path()->toString());

		$url = new Uri(['path' => ['a', 'b', 'c']]);
		$this->assertSame('a/b/c', $url->path()->toString());

		$url = new Uri(['path' => null]);
		$this->assertTrue($url->path()->isEmpty());
	}

	public function testValidQuery(): void
	{
		$url = new Uri(['query' => 'foo=bar']);
		$this->assertSame('foo=bar', $url->query()->toString());

		$url = new Uri(['query' => '?foo=bar']);
		$this->assertSame('foo=bar', $url->query()->toString());

		$url = new Uri(['query' => ['foo' => 'bar']]);
		$this->assertSame('foo=bar', $url->query()->toString());

		$url = new Uri(['query' => null]);
		$this->assertTrue($url->query()->isEmpty());
	}

	public function testValidFragment(): void
	{
		$url = new Uri(['fragment' => 'top']);
		$this->assertSame('top', $url->fragment());

		$url = new Uri(['fragment' => '#top']);
		$this->assertSame('top', $url->fragment());

		$url = new Uri(['fragment' => null]);
		$this->assertNull($url->fragment());
	}

	public function testAuth(): void
	{
		$url = new Uri(['username' => 'testuser', 'password' => 'weakpassword']);
		$this->assertSame('testuser:weakpassword', $url->auth());
	}

	public function testBase(): void
	{
		$url = new Uri(['scheme' => 'https', 'host' => 'infinri.com']);
		$this->assertSame('https://infinri.com', $url->base());

		$url->username = 'testuser';
		$url->password = 'weakpassword';

		$this->assertSame('https://testuser:weakpassword@infinri.com', $url->base());

		$url->port = 3000;
		$this->assertSame('https://testuser:weakpassword@infinri.com:3000', $url->base());
	}

	public function testBaseWithoutHost(): void
	{
		$url = new Uri();
		$this->assertNull($url->base());
	}

	public function testToArray(): void
	{
		$url = new Uri(static::$example2);
		$result = $url->toArray();
		$this->assertSame('top', $result['fragment']);
		$this->assertSame('infinri.com', $result['host']);
		$this->assertSame('weakpassword', $result['password']);
		$this->assertSame(['with' => 'infinri'], $result['params']);
		$this->assertSame(['docs', 'getting-started'], $result['path']);
		$this->assertSame(3000, $result['port']);
		$this->assertSame(['q' => 'awesome'], $result['query']);
		$this->assertSame('https', $result['scheme']);
		$this->assertSame(true, $result['slash']);
		$this->assertSame('testuser', $result['username']);
	}

	public static function buildProvider(): array
	{
		return [
			// basic 1:1 tests
			[static::$example1, [], static::$example1],
			[static::$example2, [], static::$example2],

			// relative path
			[
				'/search',
				[],
				'/search'
			],

			// relative path with trailing slash
			[
				'/search/',
				[],
				'/search/'
			],

			// relative path + adding params
			[
				'/search',
				[
					'params' => ['page' => 2],
					'query'  => ['q' => 'something']
				],
				'/search/page:2?q=something'
			],

			// relative path with trailing slash + adding params
			[
				'/search/',
				[
					'params' => ['page' => 2],
					'query'  => ['q' => 'something']
				],
				'/search/page:2/?q=something'
			],

			// relative path with colon + adding query
			[
				'/search/page:2',
				[
					'query' => ['q' => 'something']
				],
				'/search/page:2?q=something'
			],

			// path + adding params + query
			[
				'https://infinri.com/search',
				[
					'params' => ['page' => 2],
					'query'  => ['q' => 'something']
				],
				'https://infinri.com/search/page:2?q=something'
			],

			// path + params + query
			[
				'https://infinri.com/search?q=something',
				[
					'params' => ['page' => 2]
				],
				'https://infinri.com/search/page:2?q=something'
			],

			// path + slash + params + query
			[
				'https://infinri.com/search/?q=something',
				[
					'params' => ['page' => 2]
				],
				'https://infinri.com/search/page:2/?q=something'
			],

			// params replacement
			[
				'https://infinri.com/search/page:2?q=something',
				[
					'params' => ['page' => 3]
				],
				'https://infinri.com/search/page:3?q=something'
			],

			// params removal without slash
			[
				'https://infinri.com/search/page:2?q=something',
				[
					'params' => []
				],
				'https://infinri.com/search?q=something'
			],

			// params removal with slash
			[
				'https://infinri.com/search/page:2/?q=something',
				[
					'params' => []
				],
				'https://infinri.com/search/?q=something'
			],

			// URL with disabled params (treated as normal path)
			[
				'https://infinri.com/search/page:2/?q=something',
				[
					'params' => false
				],
				'https://infinri.com/search/page:2/?q=something'
			],

			// URL with disabled params without slash
			[
				'https://infinri.com/search/page:2?q=something',
				[
					'params' => false
				],
				'https://infinri.com/search/page:2?q=something'
			],
		];
	}

	#[DataProvider('buildProvider')]
	public function testToString(string $url, array $props, string $expected): void
	{
		$url = new Uri($url, $props);
		$this->assertSame($expected, $url->toString());
		$this->assertSame($expected, (string)$url);
	}

	public function testConstructParamsDisabled(): void
	{
		// with slash
		$url = new Uri('https://infinri.com/search/page:2/?q=something', ['params' => false]);
		$this->assertTrue($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('search/page:2', $url->path()->toString());

		// without slash
		$url = new Uri('https://infinri.com/search/page:2?q=something', ['params' => false]);
		$this->assertFalse($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('search/page:2', $url->path()->toString());

		// from array path
		$url = new Uri(['path' => ['search', 'page:2'], 'params' => false]);
		$this->assertFalse($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('search/page:2', $url->path()->toString());

		// without path
		$url = new Uri(['params' => false]);
		$this->assertFalse($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('', $url->path()->toString());
	}

	public function testHttps(): void
	{
		$url = new Uri(['scheme' => 'http']);
		$this->assertFalse($url->https());

		$url = new Uri(['scheme' => 'https']);
		$this->assertTrue($url->https());
	}

	public function testHasFragment(): void
	{
		$uri = new Uri('https://infinri.com/#footer');
		$this->assertTrue($uri->hasFragment());

		$uri = new Uri('https://infinri.com');
		$this->assertFalse($uri->hasFragment());
	}

	public function testHasPath(): void
	{
		$uri = new Uri('https://infinri.com/docs');
		$this->assertTrue($uri->hasPath());

		$uri = new Uri('https://infinri.com');
		$this->assertFalse($uri->hasPath());
	}

	public function testHasQuery(): void
	{
		$uri = new Uri('https://infinri.com?search=foo');
		$this->assertTrue($uri->hasQuery());

		$uri = new Uri('https://infinri.com');
		$this->assertFalse($uri->hasQuery());
	}

	public function testUnIdn(): void
	{
		$url = new Uri('https://bücher.ch');
		$this->assertSame('bücher.ch', $url->host());
		$this->assertSame('xn--bcher-kva.ch', $url->unIdn()->host());
	}

	public function testUnIdnWithInvalidHost(): void
	{
		// the host must not be dropped when the conversion fails
		$url = new Uri('https://-leading-hyphen-.example/a');
		$this->assertSame('-leading-hyphen-.example', $url->unIdn()->host());
		$this->assertSame('https://-leading-hyphen-.example/a', $url->toString());
	}
}
