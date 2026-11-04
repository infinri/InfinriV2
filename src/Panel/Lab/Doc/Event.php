<?php

namespace Infinri\Panel\Lab\Doc;

use Infinri\Panel\Lab\Doc;
use Infinri\Toolkit\A;

class Event
{
	public function __construct(
		public string $name,
		public string|null $description = null,
		public array $properties = [],
	) {
		$this->description = Doc::kt($this->description ?? '');
	}

	public static function factory(array $data): static
	{
		return new static(
			name:        $data['name'],
			description: $data['description'] ?? null,
			properties:  A::map(
				$data['properties'] ?? [],
				fn ($property) => Argument::factory($property)
			)
		);
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'description' => $this->description,
			'properties'  => $this->properties,
		];
	}
}
