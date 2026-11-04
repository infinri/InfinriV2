<?php

namespace Infinri\Text;

use AllowDynamicProperties;
use Closure;
use Infinri\Cms\App;
use Infinri\Cms\File;
use Infinri\Cms\ModelWithContent;
use Infinri\Exception\BadMethodCallException;
use Infinri\Exception\InvalidArgumentException;
use Infinri\Uuid\Uri as UuidUri;
use Infinri\Uuid\Uuid;

#[AllowDynamicProperties]
class InfinriTag
{
	public static array $aliases = [];
	public static array $types = [];

	public array $attrs = [];
	public array $data = [];
	public string $type;
	public string|null $value = null;

	public function __construct(
		string $type,
		string|null $value = null,
		array $attrs = [],
		array $data = []
	) {
		// type aliases
		if (isset(static::$types[$type]) === false) {
			if (isset(static::$aliases[$type]) === false) {
				throw new InvalidArgumentException(
					message: 'Undefined tag type: ' . $type
				);
			}

			$type = static::$aliases[$type];
		}

		$infinri    = $data['infinri'] ?? App::instance();
		$defaults = $infinri->option('infinritext.' . $type, []);
		$attrs    = array_replace($defaults, $attrs);

		// all available tag attributes
		$availableAttrs = static::$types[$type]['attr'] ?? [];

		foreach ($attrs as $attrName => $attrValue) {
			$attrName = strtolower($attrName);

			// applies only defined attributes to safely update
			if (in_array($attrName, $availableAttrs, true) === true) {
				$this->{$attrName} = $attrValue;
			}
		}

		$this->attrs   = $attrs;
		$this->data  = $data;
		$this->$type = $value;
		$this->type  = $type;
		$this->value = $value;
	}

	/**
	 * Magic data and property getter
	 */
	public function __call(string $name, array $arguments = [])
	{
		return $this->data[$name] ?? $this->$name;
	}

	/**
	 * Magic call `InfinriTag::myType($parameter1, $parameter2)`
	 */
	public static function __callStatic(string $type, array $arguments = []): string
	{
		return (new static($type, ...$arguments))->render();
	}

	public function __get(string $attr)
	{
		$attr = strtolower($attr);
		return $this->$attr ?? null;
	}

	public function attr(string $name, $default = null)
	{
		$name = strtolower($name);
		return $this->$name ?? $default;
	}

	public static function factory(...$arguments): string
	{
		return (new static(...$arguments))->render();
	}

	/**
	 * Finds a file for the given path.
	 * The method first searches the file
	 * in the current parent, if it's a page.
	 * Afterwards it uses Infinri's global file finder.
	 */
	public function file(string $path): File|null
	{
		$parent = $this->parent();

		// check first for UUID
		if (Uuid::is($path, 'file') === true) {
			if (
				is_object($parent) === true &&
				method_exists($parent, 'files') === true
			) {
				$context = $parent->files();
			}

			return Uuid::for($path, $context ?? null)->model();
		}

		if (
			is_object($parent) === true &&
			method_exists($parent, 'file') === true &&
			$file = $parent->file($path)
		) {
			return $file;
		}

		if (
			$parent instanceof File &&
			$file = $parent->page()?->file($path)
		) {
			return $file;
		}

		return $this->infinri()->file($path, null, true);
	}

	/**
	 * Returns the current Infinri instance
	 */
	public function infinri(): App
	{
		return $this->data['infinri'] ?? App::instance();
	}

	public static function parse(
		string $string,
		array $data = []
	): static {
		// remove the brackets, extract the first attribute (the tag type)
		$tag  = trim(ltrim($string, '('));

		// use substr instead of rtrim to keep non-tagged brackets
		// (link: file.pdf text: Download (PDF))
		if (str_ends_with($tag, ')') === true) {
			$tag = substr($tag, 0, -1);
		}

		$pos  = strpos($tag, ':');
		$type = trim(substr($tag, 0, $pos ?: null));
		$type = strtolower($type);
		$attr = static::$types[$type]['attr'] ?? [];

		// the type should be parsed as an attribute, so we add it here
		// to the list of possible attributes
		array_unshift($attr, $type);

		// ensure that UUIDs protocols aren't matched as attributes
		$uuids = sprintf('(?!(%s):\/\/)', implode('|', UuidUri::$schemes));

		// extract all attributes
		$regex = sprintf('/%s(%s):/i', $uuids, implode('|', $attr));
		$search = preg_split($regex, $tag, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// $search is now an array with alternating keys and values
		// convert it to arrays of keys and values
		$chunks = array_chunk($search, 2);
		$keys   = array_column($chunks, 0);
		$values = array_map('trim', array_column($chunks, 1));

		// ensure that there is a value for each key
		// otherwise combining won't work
		if (count($values) < count($keys)) {
			$values[] = '';
		}

		// combine the two arrays to an associative array
		$attributes = array_combine($keys, $values);

		// the first attribute is the type attribute
		// extract and pass its value separately
		$value = array_shift($attributes);

		return new static($type, $value, $attributes, $data);
	}

	/**
	 * Returns the parent model
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->data['parent'] ?? null;
	}

	public function render(): string
	{
		$callback = static::$types[$this->type]['html'] ?? null;

		if ($callback instanceof Closure) {
			return (string)$callback($this);
		}

		throw new BadMethodCallException(
			message: 'Invalid tag render function in tag: ' . $this->type
		);
	}

	public function type(): string
	{
		return $this->type;
	}
}
