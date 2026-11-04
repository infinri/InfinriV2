<?php

namespace Infinri\Cms\Api;

use Infinri\Cms\Api;
use Infinri\Cms\App;
use Infinri\Cms\TestCase;

class ApiCollectionTestCase extends TestCase
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
}
