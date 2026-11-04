<?php

namespace Infinri\Cms\Api;

use Infinri\Cms\Api;
use Infinri\Cms\App;
use Infinri\Cms\TestCase;

class ApiModelTestCase extends TestCase
{
	protected Api $api;

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null',
			],
		]);

		$this->api = $this->app->api();
	}

	protected function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}

	public function attr($object, $attr)
	{
		return $this->api->resolve($object)->select($attr)->toArray()[$attr];
	}

	public function assertAttr($object, $attr, $value): void
	{
		$this->assertSame($this->attr($object, $attr), $value);
	}
}
