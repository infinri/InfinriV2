<?php

namespace Infinri\Http\Request\Auth;

use Infinri\Cms\App;
use Infinri\Http\Request\Auth;
use Infinri\Session\Session;

class SessionAuth extends Auth
{
	/**
	 * Tries to return the session object
	 */
	public function session(): Session
	{
		return App::instance()->sessionHandler()->getManually($this->data);
	}

	/**
	 * Returns the session token
	 */
	public function token(): string
	{
		return $this->data;
	}

	/**
	 * Returns the authentication type
	 */
	public function type(): string
	{
		return 'session';
	}
}
