<?php

namespace Infinri\Form\Mixin;

use Infinri\Cms\App;
use Infinri\Cms\ModelWithContent;

trait Model
{
	protected ModelWithContent $model;

	/**
	 * Returns the Kirby instance
	 */
	public function kirby(): App
	{
		return $this->model->kirby();
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Sets the parent model
	 */
	protected function setModel(ModelWithContent|null $model = null): void
	{
		$this->model = $model ?? App::instance()->site();
	}
}
