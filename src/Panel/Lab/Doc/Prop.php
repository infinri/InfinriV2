<?php

namespace Infinri\Panel\Lab\Doc;

use Infinri\Panel\Lab\Doc;
use Infinri\Toolkit\A;
use Infinri\Toolkit\Str;

class Prop
{
	public function __construct(
		public string $name,
		public string|null $type = null,
		public string|null $description = null,
		public string|null $default = null,
		public string|null $example = null,
		public bool $required = false,
		public string|null $value = null,
		public array $values = []
	) {
		$this->description = Doc::kt($this->description ?? '');
	}

	public static function factory(array $data): static|null
	{
		// filter internal props
		if (isset($data['tags']['internal']) === true) {
			return null;
		}

		// filter unset props
		if (($type = $data['type']['name'] ?? null) === 'null') {
			return null;
		}

		return new static(
			name:        $data['name'],
			type:        $type,
			default:     self::normalizeDefault($data['defaultValue']['value'] ?? null, $type),
			description: $data['description'] ?? null,
			example:     $data['tags']['example'][0]['description'] ?? null,
			required:    $data['required'] ?? false,
			value:       $data['tags']['value'][0]['description'] ?? null,
			values:      $data['values'] ?? []
		);
	}

	protected static function normalizeDefault(
		string|null $default,
		string|null $type
	): string|null {
		if ($default === null) {
			// if type is boolean primarily and no default
			// value has been set, add `false` as default
			// for clarity
			if (Str::startsWith($type, 'boolean')) {
				return 'false';
			}

			return null;
		}

		// normalize longform function
		if (preg_match('/function\(\) {.*return (.*);.*}/si', $default, $matches) === 1) {
			return $matches[1];
		}

		// normalize object shorthand function
		if (preg_match('/\(\) => \((.*)\)/si', $default, $matches) === 1) {
			return $matches[1];
		}

		// normalize all other defaults from shorthand function
		if (preg_match('/\(\) => (.*)/si', $default, $matches) === 1) {
			return $matches[1];
		}

		return $default;
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'default'     => $this->default,
			'description' => $this->description,
			'example'     => $this->example,
			'required'    => $this->required,
			'type'        => $this->type,
			'value'       => $this->value,
			'values'      => $this->values,
		];
	}
}
