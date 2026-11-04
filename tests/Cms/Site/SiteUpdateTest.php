<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteUpdateTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.SiteUpdate';

	public function testUpdate(): void
	{
		$site = new Site();
		$site = $site->update([
			'copyright' => '2018'
		]);

		$this->assertSame('2018', $site->copyright()->value());
	}

	public function testUpdateHooks(): void
	{
		$calls = 0;
		$phpunit = $this;
		$input = [
			'copyright' => 'Infinri'
		];

		$this->app = $this->app->clone([
			'hooks' => [
				'site.update:before' => function (Site $site, $values, $strings) use ($phpunit, $input, &$calls) {
					$phpunit->assertNull($site->copyright()->value());
					$phpunit->assertSame($input, $values);
					$phpunit->assertSame($input, $strings);
					$calls++;
				},
				'site.update:after' => function (Site $newSite, Site $oldSite) use ($phpunit, &$calls) {
					$phpunit->assertSame('Infinri', $newSite->copyright()->value());
					$phpunit->assertNull($oldSite->copyright()->value());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('infinri');

		$site = new Site();
		$site = $site->update($input);

		$this->assertSame(2, $calls);
	}
}
