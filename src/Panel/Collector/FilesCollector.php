<?php

namespace Infinri\Panel\Collector;

use Infinri\Cms\Files;
use Infinri\Cms\Page;
use Infinri\Cms\Pages;
use Infinri\Cms\Site;
use Infinri\Cms\User;
use Infinri\Cms\Users;

class FilesCollector extends ModelsCollector
{
	public function __construct(
		protected bool $flip = false,
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|null $parent = null,
		protected string|null $query = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
		protected string|null $template = null,
	) {
	}

	protected function collect(): Files
	{
		return $this->parent()->files();
	}

	protected function collectByQuery(): Files
	{
		return $this->parent()->query($this->query, Files::class) ?? new Files([]);
	}

	protected function filter(Files|Pages|Users $models): Files
	{
		return $models->filter(function ($file) {
			// remove all protected and hidden files
			if ($file->isListable() === false) {
				return false;
			}

			// filter by template
			if ($this->template !== null && $file->template() !== $this->template) {
				return false;
			}

			return true;
		});
	}

	public function isSorting(): bool
	{
		return true;
	}

	protected function sort(Files|Pages|Users $models): Files
	{
		if ($this->sortBy === null || $this->isSearching() === true) {
			return $models->sorted();
		}

		return parent::sort($models);
	}
}
