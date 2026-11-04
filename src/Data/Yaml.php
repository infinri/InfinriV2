<?php

namespace Infinri\Data;

use Infinri\Cms\App;
use Infinri\Exception\InvalidArgumentException;

class Yaml extends Handler
{
	/**
	 * Converts an array to an encoded YAML string
	 */
	public static function encode($data): string
	{
		return match (static::handler()) {
			'symfony' => YamlSymfony::encode($data),
			default   => YamlSpyc::encode($data),
		};
	}

	/**
	 * Parses an encoded YAML string and returns a multi-dimensional array
	 */
	public static function decode($string): array
	{
		if ($string === null || $string === '') {
			return [];
		}

		if (is_array($string) === true) {
			return $string;
		}

		if (is_string($string) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid YAML data; please pass a string'
			);
		}

		return match (static::handler()) {
			'symfony' => YamlSymfony::decode($string),
			default   => YamlSpyc::decode($string)
		};
	}

	/**
	 * Returns which YAML parser (`spyc` or `symfony`)
	 * is configured to be used
	 */
	public static function handler(): string
	{
		return App::instance(null, true)?->option('yaml.handler') ?? 'spyc';
	}
}
