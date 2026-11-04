<?php

namespace Kirby\Cms;

use Kirby\Http\Visitor as BaseVisitor;
use Kirby\Toolkit\Facade;

class Visitor extends Facade
{
	public static function instance(): BaseVisitor
	{
		return App::instance()->visitor();
	}
}
