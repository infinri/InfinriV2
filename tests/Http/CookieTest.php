<?php

namespace Infinri\Http;

use Infinri\Cms\App;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cookie::class)]
class CookieTest extends TestCase
{
	protected string $cookieKey;
	protected array $cookies;

	protected function setUp(): void
	{
		$this->cookieKey = Cookie::$key;
		$this->cookies   = $_COOKIE;
	}

	protected function tearDown(): void
	{
		Cookie::$key = $this->cookieKey;
		$_COOKIE     = $this->cookies;

		App::destroy();
	}

	public function testKey(): void
	{
		$this->assertSame('InfinriHttpCookieKey', Cookie::$key);
		Cookie::$key = 'InfinriToolkitCookieKey';
		$this->assertSame('InfinriToolkitCookieKey', Cookie::$key);
	}

	public function testKeyFromAppOption(): void
	{
		Cookie::set('foo', 'bar');
		$this->assertSame('d026bf2d530669ff634253a5a78787ea32ba6301+bar', $_COOKIE['foo']);

		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'cookie' => [
					'key' => 'VerySecureLongRandomString'
				]
			]
		]);

		Cookie::set('foo', 'bar');
		$this->assertSame('bead491a9fad2a1c2cc5d337a0a42a9276ac329d+bar', $_COOKIE['foo']);
	}

	public function testLifetime(): void
	{
		$this->assertSame(253402214400, Cookie::lifetime(253402214400));
		$this->assertSame((600 + time()), Cookie::lifetime(10));
		$this->assertSame(0, Cookie::lifetime(-10));
	}

	public function testSet(): void
	{
		Cookie::set('foo', 'bar');
		$this->assertSame('d026bf2d530669ff634253a5a78787ea32ba6301+bar', $_COOKIE['foo']);
	}

	public function testForever(): void
	{
		Cookie::forever('forever', 'bar');
		$this->assertSame('d026bf2d530669ff634253a5a78787ea32ba6301+bar', $_COOKIE['forever']);
		$this->assertTrue(Cookie::exists('forever'));
	}

	public function testRemove(): void
	{
		Cookie::forever('forever', 'bar');

		$this->assertTrue(Cookie::remove('forever'));
		$this->assertFalse(isset($_COOKIE['forever']));
		$this->assertFalse(Cookie::remove('none'));
	}

	public function testExists(): void
	{
		Cookie::set('foo', 'bar');

		$this->assertTrue(Cookie::exists('foo'));
		$this->assertFalse(Cookie::exists('new'));
	}

	public function testGet(): void
	{
		Cookie::set('foo', 'bar');

		$this->assertSame('bar', Cookie::get('foo'));
		$this->assertSame('some amazing default', Cookie::get('does_not_exist', 'some amazing default'));
		$this->assertSame($_COOKIE, Cookie::get());
	}

	public function testGetSetTrack(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->assertSame([], $app->response()->usesCookies());

		Cookie::set('foo', 'fooo');
		Cookie::get('bar');

		$this->assertSame(['foo', 'bar'], $app->response()->usesCookies());
	}

	public function testParse(): void
	{
		// valid
		$_COOKIE['foo'] = 'd026bf2d530669ff634253a5a78787ea32ba6301+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// no value
		$_COOKIE['foo'] = '4bbc74219f55dfec50ce984777742ee8c3b48e5f+';
		$this->assertSame('', Cookie::get('foo'));
		$_COOKIE['foo'] = 'd026bf2d530669ff634253a5a78787ea32ba6301+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// value with a plus sign
		$_COOKIE['foo'] = 'f092351528a5044696c0faf64da08a34556ed81c+bar+baz';
		$this->assertSame('bar+baz', Cookie::get('foo'));

		// separator missing
		$_COOKIE['foo'] = 'd026bf2d530669ff634253a5a78787ea32ba6301';
		$this->assertNull(Cookie::get('foo'));
		$_COOKIE['foo'] = 'd026bf2d530669ff634253a5a78787ea32ba6301+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// no hash
		$_COOKIE['foo'] = '+bar';
		$this->assertNull(Cookie::get('foo'));
		$_COOKIE['foo'] = 'd026bf2d530669ff634253a5a78787ea32ba6301+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// wrong hash
		$_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
		$this->assertNull(Cookie::get('foo'));
	}
}
