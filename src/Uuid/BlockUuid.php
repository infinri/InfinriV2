<?php

namespace Kirby\Uuid;

use Kirby\Cms\Blocks;
use Kirby\Content\Field;
class BlockUuid extends FieldUuid
{
	protected const TYPE  = 'block';
	protected const FIELD = 'blocks';

	/**
	 * Converts content field to a Blocks collection
	 * @unstable
	 */
	public static function fieldToCollection(Field $field): Blocks
	{
		return $field->toBlocks();
	}
}
