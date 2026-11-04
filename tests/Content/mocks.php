<?php

namespace Infinri\Content;

use Exception;

/**
 * Mock for the PHP time() function to ensure reliable testing
 *
 * @return int A fake timestamp
 */
function time(): int
{
	if (defined('INFINRI_TESTING') !== true || INFINRI_TESTING !== true) {
		throw new Exception('Mock time() function was loaded outside of the test environment. This should never happen.');
	}

	return 1337;
}
