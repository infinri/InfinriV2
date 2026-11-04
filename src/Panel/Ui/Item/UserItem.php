<?php

namespace Infinri\Panel\Ui\Item;

use Infinri\Cms\ModelWithContent;
use Infinri\Cms\User;

class UserItem extends ModelItem
{
	/**
	 * @var \Infinri\Cms\User
	 */
	protected ModelWithContent $model;

	public function __construct(
		User $user,
		string|array|false|null $image = [],
		string|null $info = '{{ user.role.title }}',
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			model: $user,
			image: $image,
			info: $info,
			layout: $layout,
			text: $text ?? '{{ user.username }}',
		);
	}
}
