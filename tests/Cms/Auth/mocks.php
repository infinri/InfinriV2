<?php

namespace Infinri\Cms\Auth;

use Exception;
use Infinri\Cms\User;

class ErrorneousChallenge extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return $user->email() === 'error@getkirby.com';
	}

	public static function create(User $user, array $options): string|null
	{
		throw new Exception('An error occurred in the challenge');
	}
}
