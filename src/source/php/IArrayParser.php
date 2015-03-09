<?php 
namespace raoul2000\workflow\source\php;

/**
 * This class converts a workflow definition PHP array into its normalized form
 * as required by the WorkflowPhpSource class.
 * 
 * The normalized form apply following rules :
 * - key 'initialStatusId' : (mandatory) must contain a status Id defined in the status Id list
 * - key 'status' : (mandatory) status definition list - its value is an array where each key is a status Id
 * and each value is the status configuration
 * - all status Ids are absolute
 * 
 * TBD
 * 
 * example : 
 * [
 *   'initialStatusId' => 'WID/A'
 *   'status' => [
 *       'WID/A' => [
 *           'transition' => [
 *               'WID/B' => []
 *               'WID/C' => []
 *           ]
 *       ]
 *       'WID/B' => null
 *       'WID/C' => null
 *   ]
 * ]
 * 
 */
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
