<?php

namespace Infinri\Cms;

use Infinri\Exception\InvalidArgumentException;
use Infinri\Exception\PermissionException;
use Infinri\Toolkit\Str;

class SiteRules
{
	/**
	 * Validates if the site title can be changed
	 *
	 * @throws \Infinri\Exception\InvalidArgumentException If the title is empty
	 * @throws \Infinri\Exception\PermissionException If the user is not allowed to change the title
	 */
	public static function changeTitle(Site $site, string $title): void
	{
		if ($site->permissions()->can('changeTitle') !== true) {
			throw new PermissionException(
				key: 'site.changeTitle.permission'
			);
		}

		if (Str::length($title) === 0) {
			throw new InvalidArgumentException(
				key: 'site.changeTitle.empty'
			);
		}
	}

	/**
	 * Validates if the site can be updated
	 *
	 * @throws \Infinri\Exception\PermissionException If the user is not allowed to update the site
	 */
	public static function update(Site $site, array $content = []): void
	{
		if ($site->permissions()->can('update') !== true) {
			throw new PermissionException(
				key: 'site.update.permission'
			);
		}
	}
}
