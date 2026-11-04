<?php

namespace Infinri\Content;

use Infinri\Cms\Language;
use Infinri\Cms\ModelWithContent;
use Infinri\Exception\LogicException;

class ImmutableMemoryStorage extends MemoryStorage
{
	public function __construct(
		protected ModelWithContent $model,
		protected ModelWithContent|null $nextModel = null
	) {
		parent::__construct($model);
	}

	/**
	 * Immutable storage entries cannot be deleted
	 *
	 * @throws \Infinri\Exception\LogicException
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$this->preventMutation('deleted');
	}

	/**
	 * Immutable storage entries cannot be moved
	 *
	 * @throws \Infinri\Exception\LogicException
	 */
	public function move(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId|null $toVersionId = null,
		Language|null $toLanguage = null,
		Storage|null $toStorage = null
	): void {
		$this->preventMutation('moved');
	}

	/**
	 * Returns the next state of the model if the
	 * reference is given
	 */
	public function nextModel(): ModelWithContent|null
	{
		return $this->nextModel;
	}

	/**
	 * Throws an exception to avoid the mutation of storage data
	 *
	 * @throws \Infinri\Exception\LogicException
	 */
	protected function preventMutation(string $mutation): void
	{
		throw new LogicException(
			message: 'Storage for the ' . $this->model::CLASS_ALIAS . ' is immutable and cannot be ' . $mutation . '. Make sure to use the last alteration of the object.'
		);
	}

	/**
	 * Immutable storage entries cannot be touched
	 *
	 * @throws \Infinri\Exception\LogicException
	 */
	public function touch(VersionId $versionId, Language $language): void
	{
		$this->preventMutation('touched');
	}

	/**
	 * Immutable storage entries cannot be updated
	 *
	 * @throws \Infinri\Exception\LogicException
	 */
	public function update(VersionId $versionId, Language $language, array $fields): void
	{
		$this->preventMutation('updated');
	}
}
