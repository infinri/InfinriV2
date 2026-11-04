<?php

namespace Infinri\Panel\Areas;

class LabTest extends AreaTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testPlaygroundView(): void
	{
		$view  = $this->view('lab/components/buttons');
		$props = $view['props'];

		$this->assertSame('k-lab-playground-view', $view['component']);
		$this->assertTrue($props['compiler']);
	}

	public function testPlaygroundViewHasNoGithubButtonOrProp(): void
	{
		$view  = $this->view('lab/components/buttons');
		$props = $view['props'];

		$this->assertArrayNotHasKey('github', $props);
		$this->assertNotContains('github', array_column(array_column($props['buttons'], 'props'), 'icon'));
	}

	public function testDocsViewHasNoGithubButtonOrSourceData(): void
	{
		$view  = $this->view('lab/docs/k-button');
		$props = $view['props'];

		$this->assertSame('k-lab-docs-view', $view['component']);
		$this->assertNotContains('github', array_column(array_column($props['buttons'], 'props'), 'icon'));
		$this->assertArrayNotHasKey('source', $props['docs']);
		$this->assertArrayNotHasKey('since', $props['docs']);
		$this->assertArrayNotHasKey('deprecated', $props['docs']);
	}

	public function testPlaygroundViewWithDisabledCompiler(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'vue' => [
						'compiler' => false
					]
				]
			]
		]);

		$this->install();
		$this->login();

		$view  = $this->view('lab/components/buttons');
		$props = $view['props'];

		$this->assertFalse($props['compiler']);
	}
}
