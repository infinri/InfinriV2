<?php

namespace Infinri\Form;

use Infinri\Cms\File;
use Infinri\Cms\Language;
use Infinri\Cms\ModelWithContent;

class Form
{
	/**
	 * Fields in the form
	 */
	protected Fields $fields;

	/**
	 * Form constructor
	 */
	public function __construct(
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null
	) {
		$this->fields = new Fields(
			fields: $fields,
			model: $model,
			language: $language
		);
	}

	/**
	 * Returns an array with the default value of each field
	 */
	public function defaults(): array
	{
		return $this->fields->defaults();
	}

	/**
	 * An array of all found errors
	 */
	public function errors(): array
	{
		return $this->fields->errors();
	}

	/**
	 * Get the field object by name
	 * and handle nested fields correctly
	 *
	 * @throws \Infinri\Exception\NotFoundException
	 */
	public function field(string $name): Field|FieldClass
	{
		return $this->fields->field($name);
	}

	/**
	 * Returns form fields
	 */
	public function fields(): Fields
	{
		return $this->fields;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 */
	public function fill(
		array $input,
		bool $passthrough = true
	): static {
		$this->fields->fill(
			input:       $input,
			passthrough: $passthrough
		);
		return $this;
	}

	/**
	 * Creates a new Form instance for the given model with the fields
	 * from the blueprint and the values from the content
	 */
	public static function for(
		ModelWithContent $model,
		Language|string|null $language = null,
	): static {
		$form = new static(
			fields: $model->blueprint()->fields(),
			model: $model,
			language: $language
		);

		// fill the form with the latest content of the model
		$form->fill(input: $model->content($form->language())->toArray());

		return $form;
	}

	/**
	 * Checks if the form is invalid
	 */
	public function isInvalid(): bool
	{
		return $this->isValid() === false;
	}

	/**
	 * Checks if the form is valid
	 */
	public function isValid(): bool
	{
		return $this->fields->errors() === [];
	}

	/**
	 * Returns the language of the form
	 */
	public function language(): Language
	{
		return $this->fields->language();
	}

	/**
	 * Adds values to the passthrough array
	 * which will be added to the form data
	 * if the field does not exist
	 */
	public function passthrough(
		array|null $values = null
	): static|array {
		if ($values === null) {
			return $this->fields->passthrough();
		}

		$this->fields->passthrough(
			values: $values
		);

		return $this;
	}

	/**
	 * Resets the value of each field
	 */
	public function reset(): static
	{
		$this->fields->reset();
		return $this;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 * but only if the field is not disabled
	 *
	 * @param bool $passthrough If true, values for undefined fields will be submitted
	 * @param bool $force If true, values for fields that cannot be submitted (e.g. disabled or untranslatable fields) will be submitted
	 */
	public function submit(
		array $input,
		bool $passthrough = true,
		bool $force = false
	): static {
		$this->fields->submit(
			input: $input,
			passthrough: $passthrough,
			force: $force
		);
		return $this;
	}

	/**
	 * Converts the form to a plain array
	 */
	public function toArray(): array
	{
		$array = [
			'errors'  => $this->fields->errors(),
			'fields'  => $this->fields->toArray(),
			'invalid' => $this->isInvalid()
		];

		return $array;
	}

	/**
	 * Returns an array with the form value of each field
	 * (e.g. used as data for Panel Vue components)
	 */
	public function toFormValues(): array
	{
		return $this->fields->toFormValues();
	}

	/**
	 * Returns an array with the props of each field
	 * for the frontend
	 */
	public function toProps(): array
	{
		return $this->fields->toProps();
	}

	/**
	 * Returns an array with the stored value of each field
	 * (e.g. used for saving to content storage)
	 */
	public function toStoredValues(): array
	{
		return $this->fields->toStoredValues();
	}

	/**
	 * Validates the form and throws an exception if there are any errors
	 *
	 * @throws \Infinri\Exception\InvalidArgumentException
	 */
	public function validate(): void
	{
		$this->fields->validate();
	}
}
