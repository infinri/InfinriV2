<?php

namespace Infinri\Panel\Lab\Doc;

use Infinri\Panel\Lab\Doc;
use Infinri\Toolkit\A;

class Method
{
	public function __construct(
		public string $name,
		public string|null $description = null,
		public string|null $returns = null,
		public array $params = [],
	) {
		$this->description = Doc::kt($this->description ?? '');
	}

	public static function factory(array $data): static
	{
		return new static(
			name:        $data['name'],
			description: $data['description'] ?? null,
			returns:     $data['returns']['type']['name'] ?? null,
			params:      A::map(
				$data['params'] ?? [],
				fn ($param) => Argument::factory($param)
			),
		);
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'description' => $this->description,
			'params'      => $this->params,
			'returns'     => $this->returns,
		];
	}
}
