<?php

namespace Infinri\Cms;

use Infinri\Http\Request;
use Infinri\Toolkit\Facade;

class R extends Facade
{
	public static function instance(): Request
	{
		return App::instance()->request();
	}
}
