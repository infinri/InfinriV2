<?php

namespace Infinri\Query;

use Infinri\Cms\App;
use Infinri\Cms\Collection;
use Infinri\Cms\File;
use Infinri\Cms\Page;
use Infinri\Cms\Site;
use Infinri\Cms\User;
use Infinri\Cms\Users;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Image\QrCode;
use Infinri\Query\Runners\DefaultRunner;
use Infinri\Query\Runners\Runner;
use Infinri\Toolkit\I18n;

class Query
{
	public static array $cache = [];
	public static array $entries = [];

	public Runner $runner;

	/**
	 * Creates a new Query object
	 */
	public function __construct(
		public string|null $query = null
	) {
		if ($query !== null) {
			$this->query = trim($query);
		}

		$runner = App::instance()->option('query.runner') ?? DefaultRunner::class;

		if (is_subclass_of($runner, Runner::class) === false) {
			throw new InvalidArgumentException(message: "Query runner $runner must extend " . Runner::class);
		}

		$this->runner = $runner::for($this);
	}

	/**
	 * Creates a new Query object
	 */
	public static function factory(string|null $query): static
	{
		return new static(query: $query);
	}

	/**
	 * Method to help classes that extend Query
	 * to intercept a segment's result.
	 */
	public function intercept(mixed $result): mixed
	{
		return $result;
	}

	/**
	 * Returns the query result if anything
	 * can be found, otherwise returns null
	 *
	 * @throws \Infinri\Exception\BadMethodCallException If an invalid method is accessed by the query
	 * @throws \Infinri\Exception\InvalidArgumentException If an invalid query runner is set in the config option
	 */
	public function resolve(array|object $data = []): mixed
	{
		if (empty($this->query) === true) {
			return $data;
		}

		return $this->runner->run($this->query, (array)$data);
	}
}

/**
 * Default entries/functions
 */
Query::$entries['infinri'] = function (): App {
	return App::instance();
};

Query::$entries['collection'] = function (string $name): Collection|null {
	return App::instance()->collection($name);
};

Query::$entries['file'] = function (string $id): File|null {
	return App::instance()->file($id);
};

Query::$entries['page'] = function (string $id): Page|null {
	return App::instance()->page($id);
};

Query::$entries['qr'] = function (string $data): QrCode {
	return new QrCode($data);
};

Query::$entries['site'] = function (): Site {
	return App::instance()->site();
};

Query::$entries['t'] = function (
	string $key,
	string|array|null $fallback = null,
	string|null $locale = null
): string|null {
	return I18n::translate($key, $fallback, $locale);
};

Query::$entries['user'] = function (string|null $id = null): User|null {
	return App::instance()->user($id);
};

Query::$entries['users'] = function (): Users {
	return App::instance()->users();
};
