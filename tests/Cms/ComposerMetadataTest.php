<?php

namespace Infinri\Cms;

use Infinri\TestCase;

/**
 * Pins the removal of the upstream Composer installer plugin from the
 * package metadata. The package name is assembled at runtime so that this
 * file itself does not contain the old brand.
 */
class ComposerMetadataTest extends TestCase
{
	public const ROOT = __DIR__ . '/../..';

	protected static function installerPackage(): string
	{
		return 'get' . strrev('ybrik') . '/composer-installer';
	}

	public function testComposerJsonHasNoInstaller(): void
	{
		$json = json_decode(file_get_contents(static::ROOT . '/composer.json'), true);

		$this->assertArrayNotHasKey(static::installerPackage(), $json['require']);
		$this->assertArrayNotHasKey(static::installerPackage(), $json['config']['allow-plugins'] ?? []);
		$this->assertStringNotContainsString(static::installerPackage(), file_get_contents(static::ROOT . '/composer.json'));
	}

	public function testComposerLockHasNoInstaller(): void
	{
		$lock  = json_decode(file_get_contents(static::ROOT . '/composer.lock'), true);
		$names = array_column([...$lock['packages'], ...$lock['packages-dev']], 'name');

		$this->assertNotContains(static::installerPackage(), $names);
	}

	public function testGitignoreHasNoInstaller(): void
	{
		$this->assertStringNotContainsString(static::installerPackage(), file_get_contents(static::ROOT . '/.gitignore'));
	}

	public function testGitignoreIgnoresPlanArtifacts(): void
	{
		$lines = array_map('trim', file(static::ROOT . '/.gitignore'));

		$this->assertContains('.claude/plans/', $lines);
	}

	public function testInstalledMetadataHasNoInstaller(): void
	{
		$installed = require static::ROOT . '/vendor/composer/installed.php';
		$json      = json_decode(file_get_contents(static::ROOT . '/vendor/composer/installed.json'), true);
		$names     = array_column($json['packages'], 'name');

		$this->assertArrayNotHasKey(static::installerPackage(), $installed['versions']);
		$this->assertNotContains(static::installerPackage(), $names);
	}

	public function testInstalledRootPackage(): void
	{
		$installed = require static::ROOT . '/vendor/composer/installed.php';

		$this->assertSame('infinri/infinri', $installed['root']['name']);
		$this->assertSame('infinri', $installed['root']['type']);
	}
}
