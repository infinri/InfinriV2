<?php

namespace Infinri\Cms;

use Closure;
use Infinri\TestCase as BaseTestCase;
use Infinri\Toolkit\I18n;
use Infinri\Toolkit\Str;

class TestCase extends BaseTestCase
{
	protected Page|null $page = null;

	protected function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null'
			]
		]);

		Blueprint::$loaded = [];

		I18n::$locale       = null;
		I18n::$fallback     = 'en';
		I18n::$translations = [];
		Str::$language      = [];
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		App::destroy();
		Blueprint::$loaded = [];

		$this->tearDownTmp();

		// mock class
		ErrorLog::$log = '';
	}

	public function infinri($props = [])
	{
		return new App($props);
	}

	public function site()
	{
		return $this->infinri()->site();
	}

	public function pages()
	{
		return $this->site()->children();
	}

	public function page(string|null $id = null)
	{
		if ($id !== null) {
			return $this->site()->find($id);
		}

		if ($this->page !== null) {
			return $this->site()->find($this->page);
		}

		return $this->site()->homePage();
	}

	public function assertHooks(
		array $hooks,
		Closure $action,
		$appProps = []
	): void {
		$phpUnit   = $this;
		$triggered = 0;

		foreach ($hooks as $name => $callback) {
			$hooks[$name] = function (...$arguments) use ($callback, $phpUnit, &$triggered) {
				$callback->call($phpUnit, ...$arguments);
				$triggered++;
			};
		}

		App::destroy();

		$app = new App([
			'hooks' => $hooks,
			'roots' => ['index' => '/dev/null'],
			'user'  => 'test@infinri.com',
			'users' => [
				[
					'email' => 'test@infinri.com',
					'role'  => 'admin'
				]
			],
			...$appProps
		]);

		$action->call($this, $app);
		$this->assertSame(count($hooks), $triggered);
	}
}
