<?php

namespace Infinri\Cms\System;

use Infinri\Data\Json;
use Infinri\Filesystem\Dir;
use Infinri\Plugin\Plugin;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UpdateStatus::class)]
class UpdateStatusTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/UpdateStatusTest';
	public const TMP      = INFINRI_TMP_DIR . '/Cms.System.UpdateStatus';

	protected static string $host;
	protected static array $data = [];

	public static function setUpBeforeClass(): void
	{
		static::$host = UpdateStatus::$host;
		UpdateStatus::$host = 'file://' . static::FIXTURES . '/infinri.com';
	}

	public static function tearDownAfterClass(): void
	{
		UpdateStatus::$host = static::$host;
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testLoadData(): void
	{
		$app = $this->app('88888.8.5');

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest Infinri release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support',
					'initialRelease' => '2023-11-28'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://infinri.com/releases/{{ version }}',
					'download' => 'https://repoofthefuture.com/{{ version }}.zip',
					'upgrade' => 'https://infinri.com/releases/99999'
				]
			],
			'php' => [
				'8.0' => '2023-11-26',
				'8.1' => '2024-11-25',
				'8.2' => '2025-12-08',
				'8.3' => '2026-11-23',
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.5'
		], $app->cache('updates')->get('security'));
	}

	public function testLoadDataCacheInfinri(): void
	{
		$app = $this->app('88888.8.5');
		$app->cache('updates')->set('security', [
			'_version' => '88888.8.5',
			'versions' => [
				'*' => [
					'latest' => '88888.8.6',
					'status' => 'active-support',
					'initialRelease' => '2023-11-28'
				]
			],
			'php' => [
				'8.0' => '2023-11-26',
				'8.1' => '2024-11-25',
				'8.2' => '2025-12-08',
				'8.3' => '2026-11-23'
			],
			'urls' => [
				'*' => [
					'changes' => 'https://infinri.com/releases/{{ version }}'
				]
			]
		]);

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.6', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		// cache shouldn't be used after an update
		MockApp::$version = '88888.8.6';

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest Infinri release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support',
					'initialRelease' => '2023-11-28'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://infinri.com/releases/{{ version }}',
					'download' => 'https://repoofthefuture.com/{{ version }}.zip',
					'upgrade' => 'https://infinri.com/releases/99999'
				]
			],
			'php' => [
				'8.0' => '2023-11-26',
				'8.1' => '2024-11-25',
				'8.2' => '2025-12-08',
				'8.3' => '2026-11-23'
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.6'
		], $app->cache('updates')->get('security'));
	}

	public function testLoadDataCachePlugin(): void
	{
		$app = $this->app('88888.8.5');
		$app->cache('updates')->set('plugins/infinri/public', [
			'_version' => '88888.8.5',
			'versions' => [
				'*' => [
					'latest' => '88888.8.6',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://infinri.com/releases/{{ version }}'
				]
			]
		]);

		$plugin = new Plugin(
			name: 'infinri/public',
			info: [
				'version' => '88888.8.5'
			]
		);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertSame('88888.8.6', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		// the Infinri version shouldn't matter
		MockApp::$version = '88888.8.6';

		$updateStatus = new UpdateStatus($plugin);
		$this->assertSame('88888.8.6', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		// but the plugin version does
		$plugin = new Plugin(
			name: 'infinri/public',
			info: [
				'version' => '88888.8.6'
			]
		);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest plugin release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://example.com/public-plugin/releases/tag/{{ version }}',
					'download' => 'https://example.com/public-plugin/archive/refs/tags/{{ version }}.zip',
					'upgrade' => 'https://infinri.com/releases/99999'
				]
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.6'
		], $app->cache('updates')->get('plugins/infinri/public'));
	}

	public function testLoadDataCacheInvalid(): void
	{
		$app = $this->app('88888.8.5');
		$app->cache('updates')->set('security', 12345);

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest Infinri release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support',
					'initialRelease' => '2023-11-28'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://infinri.com/releases/{{ version }}',
					'download' => 'https://repoofthefuture.com/{{ version }}.zip',
					'upgrade' => 'https://infinri.com/releases/99999'
				]
			],
			'php' => [
				'8.0' => '2023-11-26',
				'8.1' => '2024-11-25',
				'8.2' => '2025-12-08',
				'8.3' => '2026-11-23'
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.5'
		], $app->cache('updates')->get('security'));
	}

	public function testLoadDataCacheDisabled(): void
	{
		$app = $this->app('88888.8.5')->clone([
			'options' => [
				'cache' => [
					'updates' => false
				]
			]
		]);

		$updateStatus = new UpdateStatus($app);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Cannot check for updates without a working "updates" cache'
		], $updateStatus->exceptionMessages());
	}

	public function testLoadDataNotFound(): void
	{
		$app    = $this->app('88888.8.8');
		$plugin = new Plugin(
			name: 'infinri/test',
			info: [
				'version' => '88888.8.5'
			]
		);

		// curl's wording for a missing local file differs between
		// versions ("Couldn't open file" vs. "Could not open file"),
		// so normalize it before comparing the full message
		$normalize = fn ($messages) => str_replace(
			'Could not open file',
			'Couldn\'t open file',
			$messages
		);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin infinri/test: Couldn\'t open file ' .
			static::FIXTURES . '/infinri.com/plugins/infinri/test.json'
		], $normalize($updateStatus->exceptionMessages()));

		$this->assertSame(
			'Could not load update data for plugin infinri/test: Couldn\'t open file ' .
			static::FIXTURES . '/infinri.com/plugins/infinri/test.json',
			$normalize($app->cache('updates')->get('plugins/infinri/test'))
		);

		// cached error should be used on subsequent requests
		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin infinri/test: Couldn\'t open file ' .
			static::FIXTURES . '/infinri.com/plugins/infinri/test.json'
		], $normalize($updateStatus->exceptionMessages()));
	}

	public function testLoadDataNotJson(): void
	{
		$app    = $this->app('88888.8.8');
		$plugin = new Plugin(
			name: 'infinri/invalid-json',
			info: [
				'version' => '88888.8.5'
			]
		);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin infinri/invalid-json: Invalid JSON data'
		], $updateStatus->exceptionMessages());

		$this->assertSame(
			'Could not load update data for plugin infinri/invalid-json: Invalid JSON data',
			$app->cache('updates')->get('plugins/infinri/invalid-json')
		);

		// cached error should be used on subsequent requests
		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin infinri/invalid-json: Invalid JSON data'
		], $updateStatus->exceptionMessages());
	}

	#[DataProvider('logicProvider')]
	public function testLogic(
		string $packageType,
		array $packageData,
		bool $securityOnly,
		array|null $data,
		array $expected
	): void {
		$package      = $this->$packageType(...$packageData);
		$updateStatus = new UpdateStatus($package, $securityOnly, $data);

		foreach ($expected as $method => $value) {
			$actual = $updateStatus->$method();

			// curl's wording for a missing local file differs between
			// versions ("Couldn't open file" vs. "Could not open file")
			if ($method === 'exceptionMessages') {
				$actual = str_replace(
					'Could not open file',
					'Couldn\'t open file',
					$actual
				);
			}

			$this->assertSame($value, $actual);
		}
	}

	public static function logicProvider(): array
	{
		return [
			// update check (Infinri)
			'Infinri up-to-date' => [
				'app',
				['88888.8.8'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '88888.8.8',
					'icon' => 'check',
					'label' => 'Up to date',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'up-to-date',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://infinri.com/releases/88888.8.8',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri unreleased' => [
				'app',
				['88888.8.9-rc.1'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '88888.8.9-rc.1',
					'icon' => 'question',
					'label' => 'Unreleased version',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'unreleased',
					'targetVersion' => null,
					'theme' => 'passive',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri security-update' => [
				'app',
				['77777.1.2'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://infinri.com/old-releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Infinri security-upgrade' => [
				'app',
				['55555.1.2'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '55555.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Infinri not-vulnerable (update "no-vulnerabilities")' => [
				'app',
				['77777.7.6'],
				true,
				static::data('basic'),
				[
					'currentVersion' => '77777.7.6',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://infinri.com/old-releases/77777.7.6',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri not-vulnerable (update)' => [
				'app',
				['66666.6.5'],
				true,
				static::data('basic'),
				[
					'currentVersion' => '66666.6.5',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://infinri.com/old-releases/66666.6.5',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri not-vulnerable (free major update)' => [
				'app',
				['66666.6.6'],
				true,
				static::data('basic'),
				[
					'currentVersion' => '66666.6.6',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://infinri.com/old-releases/66666.6.6',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri not-vulnerable (upgrade)' => [
				'app',
				['77777.7.7'],
				true,
				static::data('basic'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://infinri.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri update' => [
				'app',
				['77777.7.6'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '77777.7.6',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri major update (from two majors behind)' => [
				'app',
				['66666.6.6'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '66666.6.6',
					'icon' => 'info',
					'label' => 'Free update 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888.0',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri major update (no license needed)' => [
				'app',
				['77777.7.7'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Free update 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888.0',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Infinri upgrade (no newer major entry)' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-newer-major-entry'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],

			// update check (plugin)
			'Plugin up-to-date' => [
				'plugin',
				['88888.8.8'],
				false,
				static::data('basic'),
				[
					'messages' => [],
					'status' => 'up-to-date',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '88888.8.8',
						'icon' => 'check',
						'label' => 'Up to date',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'positive',
						'url' => 'https://infinri.com/releases/88888.8.8'
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin unreleased' => [
				'plugin',
				['88888.8.9-rc.1'],
				false,
				static::data('basic'),
				[
					'messages' => [],
					'status' => 'unreleased',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '88888.8.9-rc.1',
						'icon' => 'question',
						'label' => 'Unreleased version',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'passive',
						'url' => null
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin security-update' => [
				'plugin',
				['77777.1.2'],
				false,
				static::data('basic'),
				[
					'messages' => [
						[
							'text' => (
								'Your installation might be affected by the following vulnerability ' .
								'in the infinri/test plugin (high severity): Some incident'
							),
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'toArray' => [
						'currentVersion' => '77777.1.2',
						'icon' => 'alert',
						'label' => 'Free security update 77777.5.5 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'negative',
						'url' => 'https://infinri.com/old-releases/77777.5.5',
					],
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Plugin security-upgrade' => [
				'plugin',
				['55555.1.2'],
				false,
				static::data('basic'),
				[
					'messages' => [
						[
							'text' => (
								'Your installation might be affected by the following vulnerability ' .
								'in the infinri/test plugin (high severity): Some incident'
							),
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'toArray' => [
						'currentVersion' => '55555.1.2',
						'icon' => 'alert',
						'label' => 'Upgrade 88888.8.8 with security fixes available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'negative',
						'url' => 'https://infinri.com/releases/88888',
					],
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Plugin update' => [
				'plugin',
				['77777.7.6'],
				false,
				static::data('basic'),
				[
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'toArray' => [
						'currentVersion' => '77777.7.6',
						'icon' => 'info',
						'label' => 'Free update 77777.7.7 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'info',
						'url' => 'https://infinri.com/old-releases/77777.7.7',
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin major update' => [
				'plugin',
				['77777.7.7'],
				false,
				static::data('basic'),
				[
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '88888.8.8',
					'toArray' => [
						'currentVersion' => '77777.7.7',
						'icon' => 'info',
						'label' => 'Free update 88888.8.8 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'info',
						'url' => 'https://infinri.com/releases/88888.0',
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin with prefix' => [
				'plugin',
				['v77777.7.7'],
				false,
				static::data('basic'),
				[
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '88888.8.8',
					'toArray' => [
						'currentVersion' => '77777.7.7',
						'icon' => 'info',
						'label' => 'Free update 88888.8.8 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'info',
						'url' => 'https://infinri.com/releases/88888.0',
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin with invalid version' => [
				'plugin',
				['not a version'],
				false,
				static::data('basic'),
				[
					'messages' => null,
					'status' => 'error',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '?',
						'icon' => 'question',
						'label' => 'Could not check for updates',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'passive',
						'url' => 'https://infinri.com/releases/88888.8.8',
					],
					'vulnerabilities' => null,
					'exceptionMessages' => []
				]
			],
			'Plugin without version' => [
				'plugin',
				[null],
				false,
				static::data('basic'),
				[
					'messages' => null,
					'status' => 'error',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '?',
						'icon' => 'question',
						'label' => 'Could not check for updates',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'infinri/test',
						'theme' => 'passive',
						'url' => 'https://infinri.com/releases/88888.8.8',
					],
					'vulnerabilities' => null,
					'exceptionMessages' => []
				]
			],

			// vulnerabilities
			'Vulnerability sorting' => [
				'app',
				['77777.1.2'],
				false,
				static::data('incidents-severity'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (critical severity): Incident 5',
							'link' => 'https://infinri.com/security/incident-5',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Incident 1',
							'link' => 'https://infinri.com/security/incident-1',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (medium severity): Incident 4',
							'link' => 'https://infinri.com/security/incident-4',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (low severity): Incident 3',
							'link' => 'https://infinri.com/security/incident-3',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (invalid severity): Incident 2',
							'link' => 'https://infinri.com/security/incident-2',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 5',
							'fixed' => '77777.5.5',
							'link' => 'https://infinri.com/security/incident-5',
							'severity' => 'critical'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 1',
							'fixed' => '77777.5.5',
							'link' => 'https://infinri.com/security/incident-1',
							'severity' => 'high'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 4',
							'fixed' => '77777.5.5',
							'link' => 'https://infinri.com/security/incident-4',
							'severity' => 'medium'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 3',
							'fixed' => '77777.5.5',
							'link' => 'https://infinri.com/security/incident-3',
							'severity' => 'low'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 2',
							'fixed' => '77777.5.5',
							'link' => 'https://infinri.com/security/incident-2',
							'severity' => 'invalid'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Unstable release' => [
				'app',
				['77777.0.0-rc.1'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '77777.0.0-rc.1',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://infinri.com/old-releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Minimum security update' => [
				'app',
				['77777.1.2'],
				false,
				static::data('incidents-cascade'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Incident 1',
							'link' => 'https://infinri.com/security/incident-1',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=77777.2.5 || 88888.0.0 - 88888.3.2',
							'description' => 'Incident 1',
							'fixed' => '77777.2.6, 88888.3.3',
							'link' => 'https://infinri.com/security/incident-1',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Incidents with infinite loop' => [
				'app',
				['77777.1.2'],
				false,
				static::data('incidents-loop'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 88888.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5, 88888.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],

			// messages
			'Messages' => [
				'app',
				['77777.6.0'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '77777.6.0',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Some message that matches',
							'infinri' => '77777.6.0',
							'php' => '*'
						]
					],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'EOL warning (Infinri)' => [
				'app',
				['44444.1.2'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '44444.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installed Infinri version has reached end-of-life and will not receive further security updates',
							'link' => 'https://infinri.com/security/end-of-life',
							'icon' => 'bell'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'EOL warning (plugin)' => [
				'plugin',
				['44444.1.2'],
				false,
				static::data('basic'),
				[
					'currentVersion' => '44444.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability in the infinri/test plugin (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installed version of the infinri/test plugin is has reached end-of-life and will not receive further security updates',
							'link' => 'https://infinri.com/security/end-of-life',
							'icon' => 'bell'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'EOL warning (PHP)' => [
				'app',
				['88888.8.8'],
				false,
				static::data('php'),
				[
					'currentVersion' => '88888.8.8',
					'icon' => 'check',
					'label' => 'Up to date',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installed PHP release ' .
								PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION .
								' has reached end-of-life and will not receive further security updates',
							'link' => 'https://infinri.com/security/php-end-of-life',
							'icon' => 'bell'
						]
					],
					'status' => 'up-to-date',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://infinri.com/releases/88888.8.8',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'EOL warning with custom link' => [
				'app',
				['44444.1.2'],
				false,
				static::data('eol-link'),
				[
					'currentVersion' => '44444.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installed Infinri version has reached end-of-life and will not receive further security updates',
							'link' => 'https://infinri.com/44444-is-eol',
							'icon' => 'bell'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],

			// invalid/incomplete data array
			'No data' => [
				'plugin',
				['77777.7.7'],
				false,
				null,
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => null,
					'messages' => null,
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'passive',
					'url' => null,
					'vulnerabilities' => null,
					'exceptionMessages' => [
						'Could not load update data for plugin infinri/test: Couldn\'t open file ' .
						static::FIXTURES . '/infinri.com/plugins/infinri/test.json'
					]
				]
			],
			'Empty data' => [
				'app',
				['77777.7.7'],
				false,
				[],
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => null,
					'messages' => [],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'passive',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching version entry found for Infinri@77777.7.7',
						'No matching URL found for Infinri@77777.7.7'
					]
				]
			],
			'No latest version (update)' => [
				'app',
				['77777.7.5'],
				false,
				static::data('no-latest'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => null,
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No latest version (upgrade)' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-latest'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade ? available',
					'latestVersion' => null,
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => null,
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No versions' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-versions'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'passive',
					'url' => 'https://infinri.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching version entry found for Infinri@77777.7.7'
					]
				]
			],
			'No URLs' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-urls'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching URL found for Infinri@77777.7.7'
					]
				]
			],
			'No PHP' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-php'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No incidents' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-incidents'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No messages' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-messages'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Missing URL entry' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-url-entry'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching URL found for Infinri@77777.7.7'
					]
				]
			],
			'URL entry without `changes` key' => [
				'app',
				['77777.7.7'],
				false,
				static::data('url-entry-without-changes'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching URL found for Infinri@77777.7.7'
					]
				]
			],
			'Missing version entry' => [
				'app',
				['77777.7.7'],
				false,
				static::data('no-version-entry'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'passive',
					'url' => 'https://infinri.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching version entry found for Infinri@77777.7.7'
					]
				]
			],
			'Missing version entry (with vulnerability)' => [
				'app',
				['77777.4.3'],
				false,
				static::data('no-version-entry'),
				[
					'currentVersion' => '77777.4.3',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://infinri.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'passive',
					'url' => 'https://infinri.com/releases/77777.4.3',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://infinri.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => [
						'No matching version entry found for Infinri@77777.4.3'
					]
				]
			],
			'Version entry without `latest` key' => [
				'app',
				['77777.7.5'],
				false,
				static::data('version-entry-without-latest'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Invalid constraint (message)' => [
				'app',
				['77777.7.7'],
				false,
				static::data('invalid-constraint-message'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Infinri while filtering messages: ' .
						'Could not parse version constraint 77777.6.0-77777.7.9: Invalid version string "77777.6.0-77777.7.9"',
						'Error comparing version constraint for Infinri while filtering messages: ' .
						'Could not parse version constraint 7.4-8.2: Invalid version string "7.4-8.2"'
					]
				]
			],
			'Invalid constraint (incident)' => [
				'app',
				['77777.3.2'],
				false,
				static::data('invalid-constraint-incident'),
				[
					'currentVersion' => '77777.3.2',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Infinri while filtering incidents: ' .
						'Could not parse version constraint 77777.0.0-77777.5.4: Invalid version string "77777.0.0-77777.5.4"'
					]
				]
			],
			'Invalid constraint (URL entry)' => [
				'app',
				['77777.7.5'],
				false,
				static::data('invalid-constraint-url'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Infinri while finding URL: ' .
						'Could not parse version constraint invalid: Invalid version string "invalid"'
					]
				]
			],
			'Invalid constraint (version entry)' => [
				'app',
				['77777.7.5'],
				false,
				static::data('invalid-constraint-version'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Infinri while finding version entry: ' .
						'Could not parse version constraint invalid: Invalid version string "invalid"'
					]
				]
			],
			'Missing constraint (message)' => [
				'app',
				['77777.7.7'],
				false,
				static::data('missing-constraint-message'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Missing constraint infinri for Infinri while filtering messages',
						'Missing constraint php for Infinri while filtering messages'
					]
				]
			],
			'Missing constraint (incident)' => [
				'app',
				['77777.3.2'],
				false,
				static::data('missing-constraint-incident'),
				[
					'currentVersion' => '77777.3.2',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://infinri.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Missing constraint affected for Infinri while filtering incidents'
					]
				]
			]
		];
	}

	public function testMajorUpdateUsesHighestNewerEntryRegardlessOfOrderAndInitialRelease(): void
	{
		$data = [
			'latest'   => '99999.9.9',
			'versions' => [
				'99999.9.9' => ['status' => 'latest'],
				'>=77777.5.5' => ['status' => 'no-vulnerabilities'],
				'77777.*' => ['latest' => '77777.7.7', 'status' => 'active-support'],
				'88888.*' => ['latest' => '88888.8.8', 'status' => 'active-support'],
				'99999.*' => ['latest' => '99999.9.9', 'status' => 'active-support'],
			],
			'urls' => [
				'*' => [
					'changes' => 'https://infinri.com/releases/{{ version }}',
					'upgrade' => 'https://infinri.com/releases/99999'
				]
			],
			'php'       => [],
			'incidents' => [],
			'messages'  => []
		];

		$updateStatus = new UpdateStatus($this->app('77777.7.7'), false, $data);

		$this->assertSame('update', $updateStatus->status());
		$this->assertSame('99999.9.9', $updateStatus->targetVersion());
		$this->assertSame('Free update 99999.9.9 available', $updateStatus->label());
		$this->assertSame('https://infinri.com/releases/99999.0', $updateStatus->url());
	}

	public function testHostDefault(): void
	{
		$property = new \ReflectionProperty(UpdateStatus::class, 'host');
		$this->assertSame('https://infinri.com', $property->getDefaultValue());
	}

	public function testFindMaximumFreeUpdateIsRemoved(): void
	{
		$this->assertFalse(method_exists(UpdateStatus::class, 'findMaximumFreeUpdate'));
		$this->assertTrue(method_exists(UpdateStatus::class, 'findMaximumUpdate'));
	}

	public function testMessagesCache(): void
	{
		$updateStatus = new UpdateStatus($this->app('77777.6.0'), false, static::data('basic'));

		$expected = [
			[
				'text' => 'Some message that matches',
				'infinri' => '77777.6.0',
				'php' => '*'
			]
		];

		$this->assertSame($expected, $updateStatus->messages());

		// cached result should be the same
		$this->assertSame($expected, $updateStatus->messages());
	}

	protected function app(string $version): MockApp
	{
		MockApp::$version = $version;

		return new MockApp([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	protected static function data(string $name): array
	{
		if (isset(static::$data[$name]) === true) {
			return static::$data[$name];
		}

		$path = static::FIXTURES . '/logic/' . $name . '.json';
		$json = Json::read($path);

		// dynamically insert the current PHP version
		// because we cannot mock it easily
		if (isset($json['php']['<CURRENT>']) === true) {
			$json['php'][PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION] = $json['php']['<CURRENT>'];
			unset($json['php']['<CURRENT>']);
		}

		return static::$data[$name] = $json;
	}

	protected function plugin(string|null $version): Plugin
	{
		// ensure a global app object is initialized for message filtering
		$this->app('88888.8.8');

		return new Plugin(
			name: 'infinri/test',
			info: [
				'version' => $version
			]
		);
	}
}
