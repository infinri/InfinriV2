<?php

namespace Infinri\Uuid;

use Infinri\Cms\Structure;
use Infinri\Content\Field;

class StructureUuid extends FieldUuid
{
	protected const TYPE  = 'struct';
	protected const FIELD = 'structure';

	/**
	 * Converts content field to a Structure collection
	 * @unstable
	 */
	public static function fieldToCollection(Field $field): Structure
	{
		return $field->toStructure();
	}
}
