<?php

namespace Infinri\Panel\Ui\Item;

use Infinri\Cms\File;
use Infinri\Cms\ModelWithContent;
use Infinri\Panel\Model;

class FileItem extends ModelItem
{
	/**
	 * @var \Infinri\Cms\File
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Infinri\Panel\File
	 */
	protected Model $panel;

	public function __construct(
		File $file,
		protected bool $dragTextIsAbsolute = false,
		string|array|false|null $image = [],
		string|null $info = null,
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			model: $file,
			image: $image,
			info: $info,
			layout: $layout,
			text: $text ?? '{{ file.filename }}',
		);
	}

	protected function dragText(): string
	{
		return $this->panel->dragText(absolute: $this->dragTextIsAbsolute);
	}

	protected function permissions(): array
	{
		$permissions = $this->model->permissions();

		return [
			'delete' => $permissions->can('delete'),
			'sort'   => $permissions->can('sort'),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'dragText'  => $this->dragText(),
			'extension' => $this->model->extension(),
			'filename'  => $this->model->filename(),
			'mime'      => $this->model->mime(),
			'parent'    => $this->model->parent()->panel()->path(),
			'template'  => $this->model->template(),
			'url'       => $this->model->url(),
		];
	}
}
