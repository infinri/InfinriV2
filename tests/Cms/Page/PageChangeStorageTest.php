<?php

namespace Infinri\Cms;

use Infinri\Content\MemoryStorage;
use Infinri\Content\PlainTextStorage;
use Infinri\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChangeStorageTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageChangeStorage';

	public function testChangeStorage(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PlainTextStorage::class, $page->storage());

		$contentBeforeMove = $page->content()->toArray();

		$page->changeStorage(MemoryStorage::class);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());
		$this->assertSame($contentBeforeMove, $page->content()->toArray());
	}

	public function testChangeStorageWithInvalidClassName(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid storage class');

		$page->changeStorage(PageRules::class);
	}

	public function testChangeStorageWithObject(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PlainTextStorage::class, $page->storage());

		$contentBeforeMove = $page->content()->toArray();

		$storage = new MemoryStorage($page);
		$page->changeStorage($storage);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());
		$this->assertSame($contentBeforeMove, $page->content()->toArray());
	}
}
