<?php
namespace raoul2000\workflow\source\file;

use Yii;
use yii\base\Object;
use yii\helpers\VarDumper;
use raoul2000\workflow\base\WorkflowValidationException;

/**
 * A **WorkflowArrayParser** object converts a workflow definition PHP array, into its normalized form
 * as expected by the **WorkflowFileSource** class.
 *
 * The normalized form implements following rules :
 *
 * - key `initialStatusId` : (mandatory) must contain a status Id defined in the status Id list
 * - key `status` : (mandatory) status definition list - its value is an array where each key is a status Id
 * and each value is the status configuration
 * - all status Ids are absolute
 *
 * example :
 * <pre>
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
 * </pre>
 */
abstract class WorkflowArrayParser  extends Object {
	/**
	 * @var boolean when TRUE, the parse method performs some validations
	 */
	public $validate = true;
	/**
	 * Parse a workflow defined as a PHP Array.
	 *
	 * The workflow definition passed as argument is turned into an array that can be
	 * used by the WorkflowFileSource components.
	 *
	 * @param string $wId
	 * @param array $definition
	 * @param raoul2000\workflow\source\file\WorkflowFileSource $source
	 * @return array The parse workflow array definition
	 * @throws WorkflowValidationException
	 */
	abstract public function parse($wId, $definition, $source);

	/**
	 * Validates an array that contains a workflow definition.
	 *
	 * @param string $wId
	 * @param IWorkflowSource $source
	 * @param string $initialStatusId
	 * @param array $startStatusIdIndex
	 * @param array $endStatusIdIndex
	 * @throws WorkflowValidationException
	 */
	public function validate($wId, $source, $initialStatusId, $startStatusIdIndex, $endStatusIdIndex )
	{
		if ($this->validate === true) {
			if (! \in_array($initialStatusId, $startStatusIdIndex)) {
				throw new WorkflowValidationException("Initial status not defined : $initialStatusId");
			}

			// detect not defined statuses

			$missingStatusIdSuspects = \array_diff($endStatusIdIndex, $startStatusIdIndex);
			if (count($missingStatusIdSuspects) != 0) {
				$missingStatusId = [];
				foreach ($missingStatusIdSuspects as $id) {
					list ($thisWid, $thisSid) = $source->parseStatusId($id, $wId);
					if ($thisWid == $wId) {
						$missingStatusId[] = $id; // refering to the same workflow, this Id is not defined
					}
				}
				if (count($missingStatusId) != 0) {
					throw new WorkflowValidationException("One or more end status are not defined : " . VarDumper::dumpAsString($missingStatusId));
				}
			}
		}
	}
}
