<?php

namespace Infinri\Cms;

use Infinri\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePicker::class)]
class FilePickerTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.FilePicker';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg']
				],
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'd.jpg'],
							['filename' => 'e.jpg']
						]
					]
				],
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@infinri.com',
					'files' => [
						['filename' => 'f.jpg']
					]
				]
			]
		]);

		$this->app->impersonate('infinri');
	}

	public function testDefaults(): void
	{
		$picker = new FilePicker();

		$this->assertCount(3, $picker->items());
	}

	public function testQuery(): void
	{
		$picker = new FilePicker([
			'query' => 'site.files.offset(1)'
		]);

		$this->assertCount(2, $picker->items());
	}

	public function testQuerySite(): void
	{
		$picker = new FilePicker([
			'query' => 'site'
		]);

		$this->assertCount(3, $picker->items());
	}

	public function testQueryPage(): void
	{
		$picker = new FilePicker([
			'query' => 'infinri.page("test")'
		]);

		$this->assertCount(2, $picker->items());
	}

	public function testQueryUser(): void
	{
		$picker = new FilePicker([
			'query' => 'infinri.user("test")'
		]);

		$this->assertCount(1, $picker->items());
	}

	public function testQueryFile(): void
	{
		$picker = new FilePicker([
			'model' => $this->app->site()->files()->first()
		]);

		$this->assertCount(3, $picker->items());
	}

	public function testQueryInvalid(): void
	{
		$picker = new FilePicker([
			'query' => 'site.pages'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Your query must return a set of files');

		$picker->items();
	}
}
