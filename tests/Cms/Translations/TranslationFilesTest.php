<?php

namespace Infinri\Cms;

use Infinri\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Pins the content of the shipped translation files:
 * no license vocabulary and no upstream brand name
 */
class TranslationFilesTest extends TestCase
{
	public const ROOT = __DIR__ . '/../../../i18n/translations';

	public static function fileProvider(): array
	{
		$files = glob(static::ROOT . '/*.json');
		$cases = [];

		foreach ($files as $file) {
			$cases[basename($file)] = [$file];
		}

		return $cases;
	}

	/**
	 * The old brand name is assembled at runtime so that this
	 * file itself does not contain it
	 */
	protected static function legacyBrand(): string
	{
		return strrev('ybrik');
	}

	public function testAllTranslationFilesArePresent(): void
	{
		$this->assertCount(34, glob(static::ROOT . '/*.json'));
	}

	#[DataProvider('fileProvider')]
	public function testValidJson(string $file): void
	{
		$data = json_decode(file_get_contents($file), true);

		$this->assertSame(JSON_ERROR_NONE, json_last_error());
		$this->assertIsArray($data);
	}

	#[DataProvider('fileProvider')]
	public function testNoLicenseKeys(string $file): void
	{
		$keys = array_keys(json_decode(file_get_contents($file), true));

		$this->assertNotContains('license', $keys);

		$offending = array_values(array_filter(
			$keys,
			fn ($key) => str_starts_with($key, 'license.') || str_starts_with($key, 'error.license.')
		));

		$this->assertSame([], $offending);
	}

	#[DataProvider('fileProvider')]
	public function testNoLegacyBrandInKeysOrValues(string $file): void
	{
		$data  = json_decode(file_get_contents($file), true);
		$brand = static::legacyBrand();

		$offendingKeys = array_values(array_filter(
			array_keys($data),
			fn ($key) => stripos($key, $brand) !== false
		));

		$offendingValues = array_keys(array_filter(
			$data,
			fn ($value) => is_string($value) && stripos($value, $brand) !== false
		));

		$this->assertSame([], $offendingKeys);
		$this->assertSame([], $offendingValues);
	}

	public function testUpdateMessageKeysAreRenamed(): void
	{
		$data = json_decode(file_get_contents(static::ROOT . '/en.json'), true);

		$this->assertArrayHasKey('system.issues.infinri', $data);
		$this->assertArrayHasKey('system.issues.eol.infinri', $data);
		$this->assertArrayHasKey('system.issues.vulnerability.infinri', $data);
	}

	public function testAuthorCredits(): void
	{
		$read = fn (string $code) => json_decode(
			file_get_contents(static::ROOT . '/' . $code . '.json'),
			true
		)['translation.author'];

		$this->assertSame('Infinri', $read('en'));
		$this->assertSame('Infinri, Roman Steiner, Manu Moreale', $read('it'));
		$this->assertSame('Infinri, Ola Christensson', $read('sv_SE'));
	}
}
