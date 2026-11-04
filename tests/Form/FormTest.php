<?php

namespace Infinri\Form;

use Infinri\Cms\App;
use Infinri\Cms\File;
use Infinri\Cms\Language;
use Infinri\Cms\ModelWithContent;
use Infinri\Cms\Page;
use Infinri\Exception\InvalidArgumentException;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Form::class)]
class FormTest extends TestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Form.Form';

	protected ModelWithContent $model;

	protected function setUp(): void
	{
		$this->setUpSingleLanguage([
			'children' => [
				[
					'slug' => 'test'
				]
			]
		]);

		$this->model = $this->app->page('test');
		$this->setUpTmp();
	}

	protected function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}

	public function testDataWithoutFields(): void
	{
		$form = (new Form(
			fields: []
		))->fill(input: $values = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($values, $form->toStoredValues());
	}

	public function testDataFromUnsaveableFields(): void
	{
		$form = (new Form(
			fields: [
				'info' => [
					'type' => 'info',
				]
			],
			model: $this->model
		))->fill(input: [
			'info' => 'Yay'
		]);

		$this->assertArrayNotHasKey('info', $form->toStoredValues());
	}

	public function testDataFromNestedFields(): void
	{
		$form = (new Form(
			fields: [
				'structure' => [
					'type'   => 'structure',
					'fields' => [
						'tags' => [
							'type'  => 'tags',
						]
					]
				]
			],
			model: $this->model
		))->fill(input: $values = [
			'structure' => [
				[
					'tags' => 'a, b'
				]
			]
		]);

		$this->assertSame('a, b', $form->toStoredValues()['structure'][0]['tags']);
	}

	public function testDataWithCorrectFieldOrder(): void
	{
		$form = (new Form(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			model: $this->model
		))->fill(input: [
			'c' => 'C',
			'b' => 'B',
			'a' => 'A',
		])->submit(input: [
			'b' => 'B modified'
		]);

		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->toStoredValues());
		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->toFormValues());
	}

	public function testDataWithStrictMode(): void
	{
		$form = (new Form(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			model: $this->model
		))->fill(input: [
			'b' => 'B',
			'a' => 'A'
		], passthrough: false)->submit(input: [
			'c' => 'C'
		], passthrough: false);

		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->toStoredValues());
		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->toFormValues());
	}

	public function testDataWithUntranslatedFields(): void
	{
		$this->setUpMultiLanguage();

		$this->model = new Page([
			'slug' => 'test',
			'blueprint' => [
				'fields' => [
					'a' => [
						'type' => 'text'
					],
					'b' => [
						'type' => 'text',
						'translate' => false
					]
				],
			]
		]);

		// default language
		$form = Form::for(
			model: $this->model
		)->submit(input: [
			'a' => 'A',
			'b' => 'B'
		]);

		$expected = [
			'a' => 'A',
			'b' => 'B'
		];

		$this->assertSame($expected, $form->toFormValues());

		// secondary language
		$form = Form::for(
			model: $this->model,
			language: 'de'
		)->submit(input: [
			'a' => 'A',
			'b' => 'B'
		]);

		$expected = [
			'a' => 'A',
			'b' => ''
		];

		$this->assertSame($expected, $form->toFormValues());
	}

	public function testDefaults(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
					'default' => 'Test Value'
				]
			]
		);

		$this->assertSame(['test' => 'Test Value'], $form->defaults());
	}

	public function testErrors(): void
	{
		$form = (new Form(
			fields: [
				'a' => [
					'label' => 'Email',
					'type' => 'email',
				],
				'b' => [
					'label' => 'Url',
					'type' => 'url',
				]
			],
			model: $this->model
		))->fill(input: [
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertTrue($form->isInvalid());
		$this->assertFalse($form->isValid());

		$expected = [
			'a' => [
				'label' => 'Email',
				'message' => [
					'email' => 'Please enter a valid email address'
				]
			],
			'b' => [
				'label' => 'Url',
				'message' => [
					'url' => 'Please enter a valid URL'
				]
			]
		];

		$this->assertSame($expected, $form->errors());

		// check for a correct cached array
		$this->assertSame($expected, $form->errors());
	}

	public function testFieldException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field "test": The field type "does-not-exist" does not exist');

		new Form(
			fields: [
				'test' => [
					'type'  => 'does-not-exist',
					'model' => $this->model
				]
			]
		);
	}

	public function testFill(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$response = $form->fill([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testForFileWithoutBlueprint(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->model,
			'content'  => []
		]);

		$form = Form::for($file);

		$this->assertSame([], $form->toStoredValues());
	}

	public function testForWithPropsArray(): void
	{
		$this->expectException(\TypeError::class);

		Form::for($this->model, ['values' => ['a' => 'A']]);
	}

	public function testConstructorHasNoPropsParameter(): void
	{
		$names = array_map(
			fn ($parameter) => $parameter->getName(),
			(new \ReflectionMethod(Form::class, '__construct'))->getParameters()
		);

		$this->assertSame(['fields', 'model', 'language'], $names);
		$this->assertFalse(method_exists(Form::class, 'legacyConstruct'));
		$this->assertFalse(method_exists(Form::class, 'legacyFor'));
	}

	public function testDeprecatedMethodsAreRemoved(): void
	{
		foreach (['content', 'data', 'strings', 'values'] as $method) {
			$this->assertFalse(
				method_exists(Form::class, $method),
				'Form::' . $method . '() should be removed'
			);
		}
	}

	public function testForPage(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test',
				'date'  => '2012-12-12'
			],
			'blueprint' => [
				'title' => 'Test',
				'name' => 'test',
				'fields' => [
					'date' => [
						'type' => 'date'
					]
				]
			]
		]);

		$form = Form::for(
			model: $page
		)->fill(input: [
			'title' => 'Updated Title',
			'date'  => null
		]);

		$values = $form->toFormValues();

		// the title must always be transfered, even if not in the blueprint
		$this->assertSame('Updated Title', $values['title']);

		// empty fields should be actually empty
		$this->assertSame('', $values['date']);
	}

	public function testForPageWithClosureValues(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'a' => 'A'
			]
		]);

		$form = Form::for(
			model: $page
		)->fill(input: [
			'a' => fn ($value) => $value . 'A',
			'b' => fn ($value) => $value . 'B'
		]);

		$values = $form->toFormValues();

		$this->assertSame('AA', $values['a']);
		$this->assertSame('B', $values['b']);
	}

	public function testLanguage(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$this->assertInstanceOf(Language::class, $form->language());
	}

	public function testPassthrough(): void
	{
		$form = new Form();

		$response = $form->passthrough([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->passthrough());
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testReset(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$this->assertSame(['test' => ''], $form->toFormValues());

		$form->fill([
			'test' => 'Test Value'
		]);

		$this->assertSame(['test' => 'Test Value'], $form->toFormValues());

		$response = $form->reset();

		$this->assertSame($form, $response);
		$this->assertSame(['test' => ''], $form->toFormValues());
	}

	public function testSubmit(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$response = $form->submit([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testToArray(): void
	{
		$form = (new Form(
			fields: [
				'a' => [
					'label' => 'A',
					'type'  => 'text',
				],
				'b' => [
					'label' => 'B',
					'type'  => 'text',
				]
			],
			model: $this->model
		))->fill(input: [
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertSame([], $form->toArray()['errors']);
		$this->assertArrayHasKey('a', $form->toArray()['fields']);
		$this->assertArrayHasKey('b', $form->toArray()['fields']);
		$this->assertCount(2, $form->toArray()['fields']);
		$this->assertFalse($form->toArray()['invalid']);
	}

	public function testToFormValues(): void
	{
		$form = (new Form(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			]
		))->fill(input: $values = [
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertSame($values, $form->toFormValues());
	}

	public function testToProps(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'label' => 'Test',
					'type'  => 'text',
				],
			]
		);

		$this->assertSame($form->fields()->toProps(), $form->toProps());
	}

	public function testToStoredValues(): void
	{
		Field::$types['test'] = [
			'save' => function ($value) {
				return $value . ' stored';
			}
		];

		$form = (new Form(
			fields: [
				'a' => [
					'type' => 'test',
				],
				'b' => [
					'type' => 'test',
				]
			]
		))->fill(input: [
			'a' => 'A',
			'b' => 'B',
		]);

		$expected = [
			'a' => 'A stored',
			'b' => 'B stored'
		];

		$this->assertSame($expected, $form->toStoredValues());
	}

	public function testValuesWithoutFields(): void
	{
		$form = (new Form(
			fields: []
		))->fill(input: $values = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($values, $form->toFormValues());
	}
}
