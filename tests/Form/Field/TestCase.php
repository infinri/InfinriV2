<?php

namespace Infinri\Form\Field;

use Infinri\Cms\App;
use Infinri\Cms\Page;
use Infinri\Form\Field;
use Infinri\Form\Fields;
use Infinri\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected function setUp(): void
	{
		// start with a fresh set of fields
		Field::$types = [];

		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null'
			],
			'urls' => [
				'index' => 'https://infinri.com/subfolder'
			]
		]);
	}

	protected function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function app()
	{
		return $this->app;
	}

	public function field(string $type, array $attr = [], Fields|null $formFields = null)
	{
		$page = new Page(['slug' => 'test']);
		return Field::factory($type, ['model' => $page, ...$attr], $formFields);
	}
}
