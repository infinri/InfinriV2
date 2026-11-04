<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserMethodsTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.UserMethods';

	public function testUserMethods(): void
	{
		User::$methods = [
			'test' => fn () => 'homer'
		];

		$user = new User([
			'email' => 'test@infinri.com',
			'name'  => 'Test User'
		]);

		$this->assertSame('homer', $user->test());

		User::$methods = [];
	}
}
