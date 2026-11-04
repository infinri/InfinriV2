<?php

namespace Infinri\Cms;

use Infinri\Http\Visitor as BaseVisitor;
use Infinri\Toolkit\Facade;

class Visitor extends Facade
{
	public static function instance(): BaseVisitor
	{
		return App::instance()->visitor();
	}
}
