<?php

namespace Infinri\Panel\Ui\Item;

use Infinri\Cms\ModelWithContent;
use Infinri\Cms\Page;
use Infinri\Panel\Model;

class PageItem extends ModelItem
{
	/**
	 * @var \Infinri\Cms\Page
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Infinri\Panel\Page
	 */
	protected Model $panel;

	public function __construct(
		Page $page,
		string|array|false|null $image = [],
		string|null $info = null,
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			model: $page,
			image: $image,
			info: $info,
			layout: $layout,
			text: $text ?? '{{ page.title }}',
		);
	}

	protected function dragText(): string
	{
		return $this->panel->dragText();
	}

	protected function permissions(): array
	{
		$permissions = $this->model->permissions();

		return [
			'changeSlug'   => $permissions->can('changeSlug'),
			'changeStatus' => $permissions->can('changeStatus'),
			'changeTitle'  => $permissions->can('changeTitle'),
			'delete'       => $permissions->can('delete'),
			'sort'         => $permissions->can('sort'),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'dragText'    => $this->dragText(),
			'parent'      => $this->model->parentId(),
			'status'      => $this->model->status(),
			'template'    => $this->model->intendedTemplate()->name(),
			'url'         => $this->model->url(),
		];
	}
}
