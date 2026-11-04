<?php

namespace Infinri\Content;

use Infinri\Cms\Language;
use Infinri\Exception\LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ImmutableMemoryStorage::class)]
class ImmutableMemoryStorageTest extends TestCase
{
	protected $storage;

	protected function setUp(): void
	{
		parent::setUp();
		parent::setUpSingleLanguage();

		$this->storage = new ImmutableMemoryStorage($this->model);
	}

	public function testDelete(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be deleted. Make sure to use the last alteration of the object.');

		$this->storage->delete(VersionId::latest(), Language::ensure());
	}

	public function testMove(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be moved. Make sure to use the last alteration of the object.');

		$this->storage->move(
			fromVersionId: VersionId::latest(),
			fromLanguage: Language::ensure(),
			toVersionId: VersionId::changes()
		);
	}

	public function testNextModelIsRemoved(): void
	{
		$this->assertFalse(method_exists(ImmutableMemoryStorage::class, 'nextModel'));
	}

	public function testTouch(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be touched. Make sure to use the last alteration of the object.');

		$this->storage->touch(VersionId::latest(), Language::ensure());
	}

	public function testUpdate(): void
	{
		$this->storage->create(VersionId::latest(), Language::ensure(), []);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be updated. Make sure to use the last alteration of the object.');

		$this->storage->update(VersionId::latest(), Language::ensure(), []);
	}
}
