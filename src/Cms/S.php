<?php

namespace Infinri\Cms;

use Infinri\Session\Session;
use Infinri\Toolkit\Facade;

class S extends Facade
{
	public static function instance(): Session
	{
		return App::instance()->session();
	}
}
