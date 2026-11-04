<?php

namespace Infinri\Data;

use Exception;
use Infinri\Exception\BadMethodCallException;
use Infinri\Filesystem\F;
use Infinri\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PHP::class)]
class PHPTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/php';
	public const TMP      = INFINRI_TMP_DIR . '/Data.PHP';

	public function testEncode(): void
	{
		$input    = static::FIXTURES . '/input.php';
		$expected = static::FIXTURES . '/expected.php';
		$result   = PHP::encode(include $input);

		$this->assertSame(trim(file_get_contents($expected)), $result);

		// scalar values
		$this->assertSame("'test'", PHP::encode('test'));
		$this->assertSame('123', PHP::encode(123));
	}

	public function testDecode(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('The PHP::decode() method is not implemented');

		$input  = include static::FIXTURES . '/input.php';
		$result = PHP::decode($input);
	}

	public function testRead(): void
	{
		$input  = static::FIXTURES . '/input.php';
		$result = PHP::read($input);

		$this->assertSame($result, include $input);
	}

	public function testReadFileMissing(): void
	{
		$file = static::TMP . '/does-not-exist.php';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		PHP::read($file);
	}

	public function testWrite(): void
	{
		$input = include static::FIXTURES . '/input.php';
		$file  = static::TMP . '/tmp.php';

		$this->assertTrue(PHP::write($file, $input));

		$this->assertSame($input, include $file);
		$this->assertSame($input, PHP::read($file));

		F::remove($file);
	}
}
