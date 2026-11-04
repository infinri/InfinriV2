<?php

namespace Infinri\Toolkit;

class CollectionFinderTest extends TestCase
{
	public function testFindBy(): void
	{
		$collection = new Collection([
			[
				'name' => 'Bastian',
				'email' => 'bastian@infinri.com'
			],
			[
				'name' => 'Nico',
				'email' => 'nico@infinri.com'
			]
		]);

		$this->assertSame([
			'name' => 'Bastian',
			'email' => 'bastian@infinri.com'
		], $collection->findBy('email', 'bastian@infinri.com'));
	}

	public function testFindKey(): void
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('zwei', $collection->find('two'));
	}
}
