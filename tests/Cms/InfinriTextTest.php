<?php

namespace Infinri\Cms;

use Infinri\TestCase;
use Infinri\Toolkit\Html;

class InfinriTextTest extends TestCase
{
	public function testBeforeHook(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'hooks' => [
				'infinritext:before' => fn ($text) => strtolower($text)
			]
		]);

		$this->assertSame('<p>test</p>', $app->infinritext('Test'));
		// Let's see if it works twice
		$this->assertSame('<p>test</p>', $app->infinritext('Test'));
	}

	public function testGistTagWithDefaultDomains(): void
	{
		$domains = Html::$gistDomains;
		Html::$gistDomains = (new \ReflectionClass(Html::class))->getDefaultProperties()['gistDomains'];

		try {
			$app = new App(['roots' => ['index' => '/dev/null']]);

			foreach (['gist.example.com', 'gist.other.example'] as $host) {
				$html = $app->infinritext('(gist: https://' . $host . '/user/abc123)');

				$this->assertSame('', trim(strip_tags($html)));
				$this->assertStringNotContainsString('<script', $html);
			}
		} finally {
			Html::$gistDomains = $domains;
		}
	}

	public function testGistTagWithConfiguredDomain(): void
	{
		$domains = Html::$gistDomains;
		Html::$gistDomains = ['gist.example.com'];

		try {
			$app = new App(['roots' => ['index' => '/dev/null']]);

			$this->assertStringContainsString(
				'src="https://gist.example.com/user/abc123.js"',
				$app->infinritext('(gist: https://gist.example.com/user/abc123)')
			);
		} finally {
			Html::$gistDomains = $domains;
		}
	}

	public function testAfterHook(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'hooks' => [
				'infinritext:after' => fn ($text) => strip_tags($text)
			]
		]);

		$this->assertSame('Test', $app->infinritext('Test'));
		// Let's see if it works twice
		$this->assertSame('Test', $app->infinritext('Test'));
	}
}
