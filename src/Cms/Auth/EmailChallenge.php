<?php

namespace Infinri\Cms\Auth;

use Infinri\Cms\User;
use Infinri\Toolkit\I18n;
use Infinri\Toolkit\Str;

class EmailChallenge extends Challenge
{
	/**
	 * Checks whether the challenge is available
	 * for the passed user and purpose
	 *
	 * @param \Infinri\Cms\User $user User the code will be generated for
	 * @param 'login'|'password-reset'|'2fa' $mode Purpose of the code
	 */
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	/**
	 * Generates a random one-time auth code and returns that code
	 * for later verification
	 *
	 * @param \Infinri\Cms\User $user User to generate the code for
	 * @param array $options Details of the challenge request:
	 *                       - 'mode': Purpose of the code ('login', 'password-reset' or '2fa')
	 *                       - 'timeout': Number of seconds the code will be valid for
	 * @return string The generated and sent code
	 */
	public static function create(User $user, array $options): string
	{
		$code = Str::random(6, 'num');

		// insert a space in the middle for easier readability
		$formatted = substr($code, 0, 3) . ' ' . substr($code, 3, 3);

		// use the login templates for 2FA
		$mode = match($options['mode']) {
			'2fa'   => 'login',
			default => $options['mode']
		};

		$infinri = $user->infinri();
		$from  = $infinri->option(
			'auth.challenge.email.from',
			'noreply@' . $infinri->url('index', true)->host()
		);
		$name  = $infinri->option(
			'auth.challenge.email.fromName',
			$infinri->site()->title()
		);
		$subject = $infinri->option(
			'auth.challenge.email.subject',
			I18n::translate('login.email.' . $mode . '.subject', null, $user->language())
		);

		$infinri->email([
			'from'     => $from,
			'fromName' => $name,
			'to'       => $user,
			'subject'  => $subject,
			'template' => 'auth/' . $mode,
			'data' => [
				'user'    => $user,
				'site'    => $infinri->system()->title(),
				'code'    => $formatted,
				'timeout' => round($options['timeout'] / 60)
			]
		]);

		return $code;
	}
}
