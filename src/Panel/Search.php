<?php

namespace Kirby\Panel;

use Kirby\Http\Response;

class Search extends Json
{
	protected static string $key = '$search';

	public static function response($data, array $options = []): Response
	{
		if (
			is_array($data) === true &&
			array_key_exists('results', $data) === false
		) {
			$data = [
				'results'    => $data,
				'pagination' => [
					'page'      => 1,
					'limit'     => $total = count($data),
					'total'     => $total
				]
			];
		}

		return parent::response($data, $options);
	}
}
