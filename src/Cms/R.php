<?php

namespace Kirby\Cms;

use Kirby\Http\Request;
use Kirby\Toolkit\Facade;

class R extends Facade
{
	public static function instance(): Request
	{
		return App::instance()->request();
	}
}
