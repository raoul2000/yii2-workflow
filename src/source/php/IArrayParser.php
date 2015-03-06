<?php 
namespace raoul2000\workflow\source\php;

interface IArrayParser {
	/**
	 * Parse a workflow defined as a PHP Array.
	 *
	 * The workflow definition passed as argument is turned into an array that can be
	 * used by the WorkflowPhpSource components. 
	 * 
	 * @param string $wId
	 * @param array $definition
	 * @param raoul2000\workflow\source\php\WorkflowPhpSource $source
	 * @return array The parse workflow array definition
	 * @throws WorkflowValidationException
	 */
	public function parse($wId, $definition, $source);	
}
