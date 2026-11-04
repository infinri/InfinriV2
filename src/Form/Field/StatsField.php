<?php

namespace Infinri\Form\Field;

use Infinri\Form\FieldClass;
use Infinri\Panel\Ui\Stats;

class StatsField extends FieldClass
{
	/**
	 * Array or query string for reports. Each report needs a `label` and `value` and can have additional `info`, `link`, `icon` and `theme` settings.
	 */
	protected array|string $reports;

	/**
	 * The size of the report cards. Available sizes: `tiny`, `small`, `medium`, `large`
	 */
	protected string $size;

	/**
	 * Cache for the Stats UI component
	 */
	protected Stats $stats;

	public function __construct(array $params)
	{
		parent::__construct($params);

		$this->reports = $params['reports'] ?? [];
		$this->size    = $params['size']    ?? 'large';
	}

	public function hasValue(): bool
	{
		return false;
	}

	public function reports(): array
	{
		return $this->stats()->reports();
	}

	public function size(): string
	{
		return $this->stats()->size();
	}

	public function stats(): Stats
	{
		return $this->stats ??= Stats::from(
			model: $this->model,
			reports: $this->reports,
			size: $this->size
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			...$this->stats()->props()
		];
	}
}
