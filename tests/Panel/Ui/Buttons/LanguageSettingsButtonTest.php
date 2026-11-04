<?php

namespace Infinri\Panel\Ui\Buttons;

use Infinri\Cms\App;
use Infinri\Cms\Language;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageSettingsButton::class)]
class LanguageSettingsButtonTest extends TestCase
{
	public function testButton(): void
	{
		$language = new Language(['code' => 'en']);
		$button   = new LanguageSettingsButton($language);

		$this->assertSame('languages/en/update', $button->dialog);
	}

	public function testDisabled(): void
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'update' => true
						]
					]
				],
				'users/user' => [
					'name' => 'user',
					'permissions' => [
						'languages' => [
							'update' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'editor@infinri.com', 'role' => 'editor'],
				['email' => 'user@infinri.com', 'role' => 'user']
			]
		]);

		$language = new Language(['code' => 'en']);

		// not authenticated
		$button = new LanguageSettingsButton($language);
		$this->assertTrue($button->disabled);

		// with permission
		$app->impersonate('editor@infinri.com', function () use ($language) {
			$button = new LanguageSettingsButton($language);
			$this->assertFalse($button->disabled);
		});

		// without permission
		$app->impersonate('user@infinri.com', function () use ($language) {
			$button = new LanguageSettingsButton($language);
			$this->assertTrue($button->disabled);
		});
	}
}
