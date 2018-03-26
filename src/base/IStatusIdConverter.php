<?php
namespace raoul2000\workflow\base;

/**
 * The interface for status ID converters.
 *
 * A status ID converter is dedicated to provide a conversion between status ID which are valid
 * for the SimpleWorkflow behavior, and status ID that can be stored in the configured status column
 * in the underlying table.
 *
 * @see StatusIdConverter
 *
 */
interface IStatusIdConverter
{
	/**
	 * Converts the status ID passed as argument into a status ID compatible
	 * with the simpleWorkflow.
	 * 
	 * @param mixed $statusId
	 */
	public function toSimpleWorkflow($statusId);

	/**
	 * Converts the status ID passed as argument into a value that is compatible
	 * with the owner model attribute configured to store the simpleWorkflow status ID.
	 *
	 * @param mixed $statusId
	 */
	public function toModelAttribute($statusId);
}
