<?php

namespace Infinri\Http\Request\Auth;

use Infinri\Http\Request\Auth;

class BearerAuth extends Auth
{
	/**
	 * Returns the authentication token
	 */
	public function token(): string
	{
		return $this->data;
	}

	/**
	 * Returns the auth type
	 */
	public function type(): string
	{
		return 'bearer';
	}
}
