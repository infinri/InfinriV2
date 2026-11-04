<?php

namespace Infinri\Panel;

use Infinri\Cms\File as CmsFile;
use Infinri\Cms\ModelWithContent;
use Infinri\Filesystem\Asset;
use Infinri\Panel\Ui\Buttons\ViewButtons;

class Site extends Model
{
	/**
	 * @var \Infinri\Cms\Site
	 */
	protected ModelWithContent $model;

	/**
	 * Returns header buttons which should be displayed
	 * on the site view
	 */
	public function buttons(): array
	{
		return ViewButtons::view($this)->defaults(
			'open',
			'preview',
			'languages'
		)->render();
	}

	/**
	 * Returns the image file object based on provided query
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		$query ??= 'site.image';
		return parent::imageSource($query);
	}

	/**
	 * Returns the full path without leading slash
	 */
	public function path(): string
	{
		return 'site';
	}

	/**
	 * Returns the data array for the view's component props
	 */
	public function props(): array
	{
		$props = parent::props();

		return [
			...$props,
			'blueprint'   => 'site',
			'id'          => '/',
			'title'       => $this->model->title()->toString(),
			'permissions' => [
				...$props['permissions'],
				'preview' => $this->model->permissions()->can('preview') === true,
			],
		];
	}

	/**
	 * Returns the data array for this model's Panel view
	 */
	public function view(): array
	{
		return [
			'component' => 'k-site-view',
			'props'     => $this->props()
		];
	}
}
