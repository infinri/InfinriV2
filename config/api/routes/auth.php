<?php

use Infinri\Exception\InvalidArgumentException;
use Infinri\Exception\NotFoundException;

/**
 * Authentication
 */
return [
	[
		'pattern' => 'auth',
		'method'  => 'GET',
		'action'  => function () {
			if ($user = $this->infinri()->auth()->user()) {
				return $this->resolve($user)->view('auth');
			}

			throw new NotFoundException(
				message: 'The user cannot be found'
			);
		}
	],
	[
		'pattern' => 'auth/code',
		'method'  => 'POST',
		'auth'    => false,
		'action'  => function () {
			$auth = $this->infinri()->auth();

			// csrf token check
			if ($auth->type() === 'session' && $auth->csrf() === false) {
				throw new InvalidArgumentException(
					message: 'Invalid CSRF token'
				);
			}

			$user = $auth->verifyChallenge($this->requestBody('code'));

			return [
				'code'   => 200,
				'status' => 'ok',
				'user'   => $this->resolve($user)->view('auth')->toArray()
			];
		}
	],
	[
		'pattern' => 'auth/login',
		'method'  => 'POST',
		'auth'    => false,
		'action'  => function () {
			$auth    = $this->infinri()->auth();
			$methods = $this->infinri()->system()->loginMethods();

			// csrf token check
			if ($auth->type() === 'session' && $auth->csrf() === false) {
				throw new InvalidArgumentException(
					message: 'Invalid CSRF token'
				);
			}

			$email    = $this->requestBody('email');
			$long     = $this->requestBody('long');
			$password = $this->requestBody('password');

			if ($password) {
				if (isset($methods['password']) !== true) {
					throw new InvalidArgumentException(
						message: 'Login with password is not enabled'
					);
				}

				if (
					isset($methods['password']['2fa']) === true &&
					$methods['password']['2fa'] === true
				) {
					$status = $auth->login2fa($email, $password, $long);
				} else {
					$user = $auth->login($email, $password, $long);
				}
			} else {
				$mode = match (true) {
					isset($methods['code']) 		  => 'login',
					isset($methods['password-reset']) => 'password-reset',
					default => throw new InvalidArgumentException(
						message: 'Login without password is not enabled'
					)
				};

				$status = $auth->createChallenge($email, $long, $mode);
			}

			if (isset($user)) {
				return [
					'code'   => 200,
					'status' => 'ok',
					'user'   => $this->resolve($user)->view('auth')->toArray()
				];
			}

			return [
				'code'      => 200,
				'status'    => 'ok',
				'challenge' => $status->challenge()
			];
		}
	],
	[
		'pattern' => 'auth/logout',
		'method'  => 'POST',
		'auth'    => false,
		'action'  => function () {
			$this->infinri()->auth()->logout();
			return true;
		}
	],
	[
		'pattern' => 'auth/ping',
		'method'  => 'POST',
		'auth'    => false,
		'action'  => function () {
			// refresh the session timeout
			$this->infinri()->session();
			return true;
		}
	],
];
