<?php

// @codeCoverageIgnoreStart
return [
	'routes' => function ($infinri) {
		return [
			[
				'pattern' => 'query',
				'method'  => 'POST|GET',
				'auth'    => $infinri->option('kql.auth') !== false,
				'action'  => function () use ($infinri) {
					$kql = '\Infinri\Kql\Kql';

					if (class_exists($kql) === false) {
						return [
							'code'    => 500,
							'status'  => 'error',
							'message' => 'KQL plugin is not installed',
						];
					}

					$input  = $infinri->request()->get();
					$result = $kql::run($input);

					return [
						'code'   => 200,
						'result' => $result,
						'status' => 'ok',
					];
				}
			]
		];
	}
];
// @codeCoverageIgnoreEnd
