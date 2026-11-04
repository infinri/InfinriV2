<?php

namespace Infinri\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SitePurgeTest extends ModelTestCase
{
	public const TMP = INFINRI_TMP_DIR . '/Cms.SitePurge';

	public function testPurge(): void
	{
		$site = new Site();
		$site->children();
		$site->drafts();
		$site->childrenAndDrafts();

		$this->assertNotNull($site->children);
		$this->assertNotNull($site->drafts);
		$this->assertNotNull($site->childrenAndDrafts);

		$site->purge();

		$this->assertNull($site->children);
		$this->assertNull($site->drafts);
		$this->assertNull($site->childrenAndDrafts);
	}
}
