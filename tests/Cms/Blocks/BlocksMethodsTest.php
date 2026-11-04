<?php

namespace Infinri\Cms;

use Infinri\TestCase;

class BlocksMethodsTest extends TestCase
{
	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'blocksMethods' => [
				'test' => fn () => 'blocks method'
			]
		]);
	}

	public function testBlocksMethod(): void
	{
		$input = [
			['type' => 'heading']
		];

		$blocks = Blocks::factory($input);
		$this->assertSame('blocks method', $blocks->test());
	}
}
