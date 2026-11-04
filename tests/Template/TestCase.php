<?php

namespace Infinri\Template;

use Infinri\Cms\App;

class TestCase extends \Infinri\TestCase
{
	protected function tearDown(): void
	{
		while (Snippet::$current !== null) {
			Snippet::$current->close();
		}

		App::destroy();
	}
}
