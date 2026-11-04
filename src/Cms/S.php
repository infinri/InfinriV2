<?php

namespace Kirby\Cms;

use Kirby\Session\Session;
use Kirby\Toolkit\Facade;

class S extends Facade
{
	public static function instance(): Session
	{
		return App::instance()->session();
	}
}
