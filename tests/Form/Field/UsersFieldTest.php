<?php

namespace Infinri\Form\Field;

use Infinri\Cms\App;
use Infinri\Cms\Page;

class UsersFieldTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'leonardo@infinri.com',
					'role'  => 'admin'
				],
				[
					'email' => 'raphael@infinri.com',
					'role'  => 'admin'
				],
				[
					'email' => 'michelangelo@infinri.com',
					'role'  => 'admin'
				],
				[
					'email' => 'donatello@infinri.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('infinri');
	}

	public function testDefaultProps(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test'])
		]);

		$this->assertSame('users', $field->type());
		$this->assertSame('users', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertNull($field->max());
		$this->assertTrue($field->multiple());
		$this->assertTrue($field->hasValue());
	}

	public function testDefaultUser(): void
	{
		$this->app->impersonate('raphael@infinri.com');

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test'])
		]);

		$this->assertSame([], $field->default());
	}

	public function testCurrentDefaultUser(): void
	{
		$this->app->impersonate('raphael@infinri.com');

		$field = $this->field('users', [
			'model'   => new Page(['slug' => 'test']),
			'default' => true
		]);

		$this->assertSame('raphael@infinri.com', $field->default()[0]['email']);
	}

	public function testMultipleDefaultUsers(): void
	{
		$this->app->impersonate('raphael@infinri.com');

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'default' => [
				'raphael@infinri.com',
				'donatello@infinri.com'
			]
		]);

		$this->assertSame('raphael@infinri.com', $field->default()[0]['email']);
		$this->assertSame('donatello@infinri.com', $field->default()[1]['email']);
	}

	public function testDefaultUserDisabled(): void
	{
		$this->app->impersonate('raphael@infinri.com');

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'default' => false
		]);

		$this->assertSame([], $field->default());
	}

	public function testValue(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'value' => [
				'leonardo@infinri.com', // exists
				'raphael@infinri.com', // exists
				'homer@infinri.com'  // does not exist
			]
		]);

		$value = $field->value();
		$ids   = array_column($value, 'email');

		$expected = [
			'leonardo@infinri.com',
			'raphael@infinri.com'
		];

		$this->assertSame($expected, $ids);
	}

	public function testValueWithProtectedUser(): void
	{
		// the permission cache is keyed by role,
		// so it needs to be unique for this test
		$uuid = uuid();

		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name'        => 'editor-' . $uuid,
					'permissions' => [
						'users' => ['list' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@infinri.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'id'    => 'admin',
					'email' => 'admin@infinri.com',
					'role'  => 'admin'
				]
			],
			'user' => 'editor@infinri.com'
		]);

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'store' => 'id',
			'value' => ['editor@infinri.com', 'admin@infinri.com']
		]);

		$value = $field->value();

		// the editor may list themselves
		$this->assertSame('editor', $value[0]['id']);
		$this->assertSame('editor@infinri.com', $value[0]['email']);

		// the user that must not be listed only echoes back
		// the ID that is already stored in the content file
		$this->assertSame(['id', 'uuid', 'image', 'info', 'link', 'permissions', 'sortable', 'text'], array_keys($value[1]));
		$this->assertSame('admin@infinri.com', $value[1]['id']);
		$this->assertSame('–', $value[1]['text']);
		$this->assertFalse($value[1]['link']);

		// ... and is still kept when the value is stored again
		$this->assertSame(['editor', 'admin@infinri.com'], $field->toStoredValue());
	}

	public function testMin(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'value' => [
				'leonardo@infinri.com',
				'raphael@infinri.com'
			],
			'min' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(3, $field->min());
		$this->assertTrue($field->required());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'value' => [
				'leonardo@infinri.com',
				'raphael@infinri.com'
			],
			'max' => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testEmpty(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('users', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('users', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('tags', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true,
			'value' => [
				'leonardo@infinri.com',
			],
		]);

		$this->assertTrue($field->isValid());
	}

	public function testApi(): void
	{
		$app = $this->app->clone([
			'options' => ['api.allowImpersonation' => true],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'blueprint' => [
							'title' => 'Test',
							'name' => 'test',
							'fields' => [
								'authors' => [
									'type' => 'users',
								]
							]
						]
					]
				]
			]
		]);

		$app->impersonate('infinri');
		$api = $app->api()->call('pages/test/fields/authors');

		$this->assertCount(2, $api);
		$this->assertArrayHasKey('data', $api);
		$this->assertArrayHasKey('pagination', $api);
		$this->assertCount(4, $api['data']);
		$this->assertSame('donatello@infinri.com', $api['data'][0]['email']);
		$this->assertSame('leonardo@infinri.com', $api['data'][1]['email']);
		$this->assertSame('michelangelo@infinri.com', $api['data'][2]['email']);
		$this->assertSame('raphael@infinri.com', $api['data'][3]['email']);
	}
}
