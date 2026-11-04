<?php

namespace Infinri\Panel\Controller;

use Infinri\Cms\App;
use Infinri\Cms\Find;
use Infinri\Cms\Page;
use Infinri\Cms\Site;
use Infinri\Toolkit\I18n;

class PageTree
{
	protected Site $site;

	public function __construct(
	) {
		$this->site = App::instance()->site();
	}

	/**
	 * Returns children for the parent as entries
	 */
	public function children(
		string|null $parent = null,
		string|null $moving = null
	): array {
		if ($moving !== null) {
			$moving = Find::parent($moving);
		}

		if ($parent === null) {
			return [
				$this->entry($this->site, $moving)
			];
		}

		return Find::parent($parent)
			->childrenAndDrafts()
			->filterBy('isListable', true)
			->values(
				fn ($child) => $this->entry($child, $moving)
			);
	}

	/**
	 * Returns the properties to display the site or page
	 * as an entry in the page tree component
	 */
	public function entry(
		Site|Page $entry,
		Page|null $moving = null
	): array {
		$panel = $entry->panel();
		$id    = $entry->id() ?? '/';
		$uuid  = $entry->uuid()?->toString();
		$url   = $entry->url();
		$value = $uuid ?? $id;

		return [
			'children'    => $panel->url(true),
			'disabled'    => $moving?->isMovableTo($entry) === false,
			'hasChildren' =>
				$entry->hasChildren() === true ||
				$entry->hasDrafts() === true,
			'icon'        => match (true) {
				$entry instanceof Site => 'home',
				default                => $panel->image()['icon'] ?? null
			},
			'id'          => $id,
			'open'        => false,
			'label'       => match (true) {
				$entry instanceof Site => I18n::translate('view.site'),
				default                => $entry->title()->value()
			},
			'url'         => $url,
			'uuid'        => $uuid,
			'value'       => $value
		];
	}

	/**
	 * Returns the UUIDs/ids for all parents of the page
	 */
	public function parents(
		string|null $page = null,
		bool $includeSite = false,
	): array {
		$page      = $this->site->page($page);
		$parents   = $page?->parents()->flip();
		$parents   = $parents?->values(
			fn ($parent) => $parent->uuid()?->toString() ?? $parent->id()
		);
		$parents ??= [];

		if ($includeSite === true) {
			array_unshift($parents, $this->site->uuid()?->toString() ?? '/');
		}

		return [
			'data' => $parents
		];
	}
}
