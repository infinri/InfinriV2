<?php

namespace Infinri\Cms;

use Infinri\Toolkit\I18n;
use Infinri\Toolkit\Str;

class ModelTestCase extends TestCase
{
	public function cleanUp(): void
	{
		// clean up after previous app instances
		App::destroy();

		// discard all cached blueprints
		Blueprint::$loaded = [];

		I18n::$locale       = null;
		I18n::$fallback     = 'en';
		I18n::$translations = [];
		Str::$language      = [];
	}

	protected function setUp(): void
	{
		$this->cleanUp();

		$this->setUpTmp();
		$this->setUpSingleLanguage();

		$this->app->impersonate('kirby');
	}

	protected function tearDown(): void
	{
		$this->cleanUp();
		$this->tearDownTmp();
	}
}
