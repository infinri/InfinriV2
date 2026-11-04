<?php

use Infinri\Toolkit\I18n;

/**
 * @var \Infinri\Cms\User $user
 * @var string $site
 * @var string $code
 * @var int $timeout
 */
echo I18n::template(
	'login.email.login.body',
	null,
	compact('user', 'site', 'code', 'timeout'),
	$user->language()
);
