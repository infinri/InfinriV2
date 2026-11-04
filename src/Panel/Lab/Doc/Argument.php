<?php

namespace Infinri\Panel\Lab\Doc;

use Infinri\Panel\Lab\Doc;

class Argument
{
	public function __construct(
		public string $name,
		public string|null $type = null,
		public string|null $description = null,
	) {
		$this->description = Doc::kt($this->description ?? '', true);
	}

	public static function factory(array $data): static
	{
		return new static(
			name:        $data['name'],
			type:        $data['type']['names'][0] ?? null,
			description: $data['description'] ?? null,
		);
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'description' => $this->description,
			'type'        => $this->type,
		];
	}
}
