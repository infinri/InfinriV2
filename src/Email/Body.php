<?php

namespace Infinri\Email;

use Infinri\Toolkit\Properties;

class Body
{
	protected string|null $html;
	protected string|null $text;

	/**
	 * Email body constructor
	 */
	public function __construct(array $props = [])
	{
		$this->html = $props['html'] ?? null;
		$this->text = $props['text'] ?? null;
	}

	/**
	 * Returns the HTML content of the email body
	 */
	public function html(): string
	{
		return $this->html ?? '';
	}

	/**
	 * Returns the plain text content of the email body
	 */
	public function text(): string
	{
		return $this->text ?? '';
	}

	public function toArray(): array
	{
		return [
			'html' => $this->html(),
			'text' => $this->text()
		];
	}
}
