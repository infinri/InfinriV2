<?php

namespace Infinri\Cms;

use Infinri\Cms\Api\ApiCollectionTestCase;
use Infinri\Filesystem\Dir;

class UsersApiCollectionTest extends ApiCollectionTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.UsersApiCollection';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				['email' => 'a@infinri.com'],
				['email' => 'b@infinri.com']
			]
		]);

		$this->api = $this->app->api();
		Dir::make(static::TMP);

		$this->app->impersonate('infinri');
	}

	public function testDefaultCollection(): void
	{
		$collection = $this->api->collection('users');
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('a@infinri.com', $result[0]['email']);
		$this->assertSame('b@infinri.com', $result[1]['email']);
	}

	public function testPassedCollection(): void
	{
		$collection = $this->api->collection('users', $this->app->users()->offset(1));
		$result     = $collection->toArray();

		$this->assertCount(1, $result);
		$this->assertSame('b@infinri.com', $result[0]['email']);
	}
}
