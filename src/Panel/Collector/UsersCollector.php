<?php

namespace Infinri\Panel\Collector;

use Infinri\Cms\App;
use Infinri\Cms\Files;
use Infinri\Cms\Page;
use Infinri\Cms\Pages;
use Infinri\Cms\Site;
use Infinri\Cms\User;
use Infinri\Cms\Users;

class UsersCollector extends ModelsCollector
{
	public function __construct(
		protected bool $flip = false,
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|null $parent = null,
		protected string|null $query = null,
		protected string|null $role = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
	) {
	}

	protected function collect(): Users
	{
		return App::instance()->users();
	}

	protected function collectByQuery(): Users
	{
		return $this->parent()->query($this->query, Users::class) ?? new Users([]);
	}

	protected function filter(Files|Pages|Users $models): Users
	{
		$user = App::instance()->user();

		if ($user === null) {
			return new Users([]);
		}

		if ($user->role()->permissions()->for('access', 'users') === false) {
			return new Users([]);
		}

		$models = $models->filter('isListable', true);

		if ($this->role !== null) {
			$models = $models->role($this->role);
		}

		return $models;
	}
}
