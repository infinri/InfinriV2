<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteMethodsTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.SiteMethods';

	public function testSiteMethod(): void
	{
		new App([
			'siteMethods' => [
				'test' => fn () => 'site method'
			]
		]);

		$site = new Site();
		$this->assertSame('site method', $site->test());
	}
}
