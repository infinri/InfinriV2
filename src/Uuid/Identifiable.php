<?php

namespace Kirby\Uuid;

interface Identifiable
{
	public function id();
	public function uuid(): Uuid|null;
}
