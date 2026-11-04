<?php

namespace Kirby\Scripts;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StripDocblocksTest extends TestCase
{
	protected string $tmp;

	protected function setUp(): void
	{
		$this->tmp = sys_get_temp_dir() . '/strip-docblocks-' . uniqid();
		mkdir($this->tmp);
	}

	protected function tearDown(): void
	{
		exec('rm -rf ' . escapeshellarg($this->tmp));
	}

	protected function fixture(string $relative, string $code): string
	{
		$file = $this->tmp . '/' . $relative;
		@mkdir(dirname($file), 0777, true);
		file_put_contents($file, $code);
		return $file;
	}

	protected function strip(string ...$paths): string
	{
		$script = dirname(__DIR__, 2) . '/strip-docblocks.php';
		exec('php ' . escapeshellarg($script) . ' ' . implode(' ', array_map('escapeshellarg', $paths)), $out, $code);
		$this->assertSame(0, $code);
		return implode("\n", $out);
	}

	public static function tagProvider(): array
	{
		return [['@package'], ['@author'], ['@link'], ['@copyright'], ['@license']];
	}

	#[DataProvider('tagProvider')]
	public function testRemovesDocblockWithTag(string $tag): void
	{
		$file = $this->fixture('A.php', "<?php\n\n/**\n * Desc\n *\n * $tag Foo\n */\nclass A\n{\n}\n");
		$this->strip($this->tmp);
		$this->assertSame("<?php\n\nclass A\n{\n}\n", file_get_contents($file));
	}

	public function testKeepsDocblockWithoutTags(): void
	{
		$code = "<?php\n\n/**\n * Desc\n * @param string \$a\n */\nfunction a(\$a) {}\n";
		$file = $this->fixture('A.php', $code);
		$this->strip($this->tmp);
		$this->assertSame($code, file_get_contents($file));
	}

	public function testRemovesIndentationAndLineBreak(): void
	{
		$file = $this->fixture('A.php', "<?php\n\nclass A\n{\n\t/**\n\t * @author Foo\n\t */\n\tpublic \$a;\n}\n");
		$this->strip($this->tmp);
		$this->assertSame("<?php\n\nclass A\n{\n\tpublic \$a;\n}\n", file_get_contents($file));
	}

	public function testRemovesCrlfLineBreak(): void
	{
		$file = $this->fixture('A.php', "<?php\r\n/**\r\n * @license MIT\r\n */\r\nclass A {}\r\n");
		$this->strip($this->tmp);
		$this->assertSame("<?php\r\nclass A {}\r\n", file_get_contents($file));
	}

	public function testIgnoresStringsAndPlainComments(): void
	{
		$code = "<?php\n\$a = '/** @author Foo */';\n/* @license MIT */\n// @link x\n";
		$file = $this->fixture('A.php', $code);
		$this->strip($this->tmp);
		$this->assertSame($code, file_get_contents($file));
	}

	public function testSkipsVendorAndDependencies(): void
	{
		$code = "<?php\n/**\n * @license MIT\n */\nclass A {}\n";
		$vendor = $this->fixture('vendor/x/A.php', $code);
		$deps   = $this->fixture('dependencies/x/A.php', $code);
		$this->strip($this->tmp);
		$this->assertSame($code, file_get_contents($vendor));
		$this->assertSame($code, file_get_contents($deps));
	}

	public function testDoesNotRewriteUnmatchedFile(): void
	{
		$file = $this->fixture('A.php', "<?php\nclass A {}\n");
		touch($file, 1000000000);
		clearstatcache();
		$output = $this->strip($this->tmp);
		clearstatcache();
		$this->assertSame(1000000000, filemtime($file));
		$this->assertStringNotContainsString('A.php', $output);
	}

	public function testAcceptsSingleFileArgument(): void
	{
		$file = $this->fixture('A.php', "<?php\n/**\n * @link x\n */\nclass A {}\n");
		$this->strip($file);
		$this->assertSame("<?php\nclass A {}\n", file_get_contents($file));
	}

	public function testOutputListsFilesAndCount(): void
	{
		$a = $this->fixture('A.php', "<?php\n/** @author x */\nclass A {}\n");
		$b = $this->fixture('B.php', "<?php\n/** @link x */\nclass B {}\n");
		$this->fixture('C.php', "<?php\nclass C {}\n");
		$output = $this->strip($this->tmp);
		$this->assertStringContainsString($a, $output);
		$this->assertStringContainsString($b, $output);
		$this->assertStringEndsWith('2 file(s) updated', $output);
	}
}
