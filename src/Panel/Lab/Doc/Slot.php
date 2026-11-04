<?php

namespace Infinri\Panel\Lab\Doc;

use Infinri\Panel\Lab\Doc;
use Infinri\Toolkit\A;

class Slot
{
	public function __construct(
		public string $name,
		public string|null $description = null,
		public array $bindings = [],
	) {
		$this->description = Doc::kt($this->description ?? '');
	}

	public static function factory(array $data): static
	{
		return new static(
			name:        $data['name'],
			description: $data['description'] ?? null,
			bindings:    A::map(
				$data['bindings'] ?? [],
				fn ($binding) => Argument::factory($binding)
			)
		);
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'bindings'    => $this->bindings,
			'description' => $this->description,
		];
	}
}
