<?php

namespace Infinri\Uuid;

use Generator;
use Infinri\Cms\App;
use Infinri\Cms\Page;

class PageUuid extends ModelUuid
{
	protected const TYPE = 'page';

	/**
	 * Removes the current UUID from cache,
	 * recursively including all children if needed
	 */
	public function clear(bool $recursive = false): bool
	{
		/**
		 * If $recursive, also clear UUIDs from cache for all children
		 * @var \Infinri\Cms\Page $model
		 */
		if ($recursive === true && $model = $this->model()) {
			foreach ($model->childrenAndDrafts() as $child) {
				$child->uuid()->clear(true);
			}
		}

		return parent::clear();
	}

	/**
	 * Looks up UUID in cache and resolves
	 * to page object
	 */
	protected function findByCache(): Page|null
	{
		if ($key = $this->key()) {
			if ($value = Uuids::cache()->get($key)) {
				$page = App::instance()->page($value);

				// the cached path can be stale,
				// e.g. when the page has been moved or copied
				if ($this->isFor($page) === true) {
					return $page;
				}
			}
		}

		return null;
	}

	/**
	 * Generator for all pages and drafts in the site
	 *
	 * @return \Generator|\Infinri\Cms\Page[]
	 */
	public static function index(Page|null $entry = null): Generator
	{
		$entry ??= App::instance()->site();

		foreach ($entry->childrenAndDrafts() as $page) {
			yield $page;
			yield from static::index($page);
		}
	}

	/**
	 * Feeds the UUID for the page (and optionally
	 * its children) into the cache
	 */
	public function populate(
		bool $force = false,
		bool $recursive = false
	): bool {
		/**
		 * If $recursive, also populate UUIDs for all children
		 * @var \Infinri\Cms\Page $model
		 */
		if ($recursive === true && $model = $this->model()) {
			foreach ($model->childrenAndDrafts() as $child) {
				$child->uuid()->populate($force, true);
			}
		}

		return parent::populate($force);
	}

	/**
	 * Returns permalink url
	 */
	public function toPermalink(): string
	{
		// make sure UUID is cached because the permalink
		// route only looks up UUIDs from cache
		if ($this->isCached() === false) {
			$this->populate();
		}

		$infinri = App::instance();
		$url   = $infinri->url();

		if ($language = $infinri->language('current')) {
			$url = $language->url();
		}

		return $url . '/@/' . static::TYPE . '/' . $this->id();
	}
}
