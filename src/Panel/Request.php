<?php

namespace Infinri\Panel;

use Infinri\Http\Response;

class Request
{
	/**
	 * Renders request responses
	 */
	public static function response($data, array $options = []): Response
	{
		// an empty list is a valid result for requests
		if ($data === []) {
			return Panel::json([]);
		}

		$data = Json::responseData($data);
		return Panel::json($data, $data['code'] ?? 200);
	}
}
