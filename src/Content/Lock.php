<?php

namespace Infinri\Content;

use Infinri\Cms\App;
use Infinri\Cms\Language;
use Infinri\Cms\Languages;
use Infinri\Cms\User;
use Infinri\Toolkit\Str;

class Lock
{
	public function __construct(
		protected User|null $user = null,
		protected int|null $modified = null
	) {
	}

	/**
	 * Creates a lock for the given version by
	 * reading the modification timestamp and
	 * lock user id from the version.
	 */
	public static function for(
		Version $version,
		Language|string $language = 'default'
	): static {
		// wildcard to search for a lock in any language
		// the first locked one will be preferred
		if ($language === '*') {
			foreach (Languages::ensure() as $language) {
				$lock = static::for($version, $language);

				// return the first locked lock if any exists
				if ($lock->isLocked() === true) {
					return $lock;
				}
			}

			// no language has an active lock:
			// fall back to the lock for the current language,
			// so that the user and modification timestamp
			// reflect the current version and not the
			// arbitrary last-iterated language
			return static::for($version, Language::ensure());
		}

		$language = Language::ensure($language);

		// if the version does not exist, it cannot be locked
		if ($version->exists($language) === false) {
			// create an open lock for the current user
			return new static(
				user: App::instance()->user(),
			);
		}

		// Read the locked user id from the version
		if ($userId = ($version->read($language)['lock'] ?? null)) {
			$user = App::instance()->user($userId);
		}

		return new static(
			user: $user ?? null,
			modified: $version->modified($language)
		);
	}

	/**
	 * Checks if the lock is still active because
	 * recent changes have been made to the content
	 */
	public function isActive(): bool
	{
		$minutes = 10;
		return $this->modified > time() - (60 * $minutes);
	}

	/**
	 * Checks if content locking is enabled at all
	 */
	public static function isEnabled(): bool
	{
		return App::instance()->option('content.locking', true) !== false;
	}

	/**
	 * Checks if the lock is actually locked
	 */
	public function isLocked(): bool
	{
		// if locking is disabled globally,
		// the lock is always open
		if (static::isEnabled() === false) {
			return false;
		}

		if ($this->user === null) {
			return false;
		}

		// the version is not locked if the editing user
		// is the currently logged in user
		if ($this->user === App::instance()->user()) {
			return false;
		}

		// check if the lock is still active due to the
		// content currently being edited.
		if ($this->isActive() === false) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the timestamp when the locked content has
	 * been updated. You can pass a format to get a useful,
	 * formatted date back.
	 */
	public function modified(
		string|null $format = null,
		string|null $handler = null
	): int|string|false|null {
		if ($this->modified === null) {
			return null;
		}

		return Str::date($this->modified, $format, $handler);
	}

	/**
	 * Converts the lock info to an array. This is directly
	 * usable for Panel view props.
	 */
	public function toArray(): array
	{
		$user = $this->user?->isListable() === true ? $this->user : null;

		return [
			'isLocked' => $this->isLocked(),
			'modified' => $this->modified('c', 'date'),
			'user'     => [
				'id'    => $user?->id(),
				'email' => $user?->email()
			]
		];
	}

	/**
	 * Returns the user to whom this lock belongs
	 */
	public function user(): User|null
	{
		return $this->user;
	}
}
