<?php

namespace Infinri\Panel;

use Infinri\Cms\Collection;
use Infinri\Cms\File;
use Infinri\Cms\Page;
use Infinri\Cms\User;
use Infinri\Content\Changes;
use Infinri\Panel\Ui\Item\FileItem;
use Infinri\Panel\Ui\Item\PageItem;
use Infinri\Panel\Ui\Item\UserItem;

class ChangesDialog
{
	public function __construct(
		protected Changes $changes = new Changes()
	) {
	}

	/**
	 * Returns the item props for all changed files
	 */
	public function files(): array
	{
		return $this->items($this->changes->files()->filter('isListable', true));
	}

	/**
	 * Helper method to return item props for a single given model
	 */
	public function item(File|Page|User $model): array
	{
		$item = match (true) {
			$model instanceof File => new FileItem(file: $model),
			$model instanceof Page => new PageItem(page: $model),
			$model instanceof User => new UserItem(user: $model),
		};

		return $item->props();
	}

	/**
	 * Helper method to return item props for the given models
	 */
	public function items(Collection $models): array
	{
		return $models->values($this->item(...));
	}

	/**
	 * Returns the backend full definition for dialog
	 */
	public function load(): array
	{
		if ($this->changes->cacheExists() === false) {
			$this->changes->generateCache();
		}

		return [
			'component' => 'k-changes-dialog',
			'props'     => [
				'files' => $this->files(),
				'pages' => $this->pages(),
				'users' => $this->users(),
			]
		];
	}

	/**
	 * Returns the item props for all changed pages
	 */
	public function pages(): array
	{
		return $this->items($this->changes->pages()->filter('isListable', true));
	}

	/**
	 * Returns the item props for all changed users
	 */
	public function users(): array
	{
		return $this->items($this->changes->users()->filter('isListable', true));
	}
}
