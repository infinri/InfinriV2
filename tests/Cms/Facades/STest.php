<?php

namespace Infinri\Cms;

use Infinri\Filesystem\Dir;

class STest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.STest';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testInstance(): void
	{
		$this->assertSame($this->app->session(), S::instance());
	}
}
