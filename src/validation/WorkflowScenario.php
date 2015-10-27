<?php
namespace raoul2000\workflow\validation;

use raoul2000\workflow\base\WorkflowException;

/**
 * Helper class to create workflow scenario names.
 *
 */
class WorkflowScenario
{
	const ANY_STATUS = '*';
	const ANY_WORKFLOW = '*';

	/**
	 * Returns the scenario name for a change status action.
	 * 
	 * @param string $start the absolute start status Id
	 * @param string $end the absolute end status Id
	 * @throws WorkflowException
	 * @return string the scenario name
	 */
	public static function changeStatus($start, $end)
	{
		if ( empty($start) || ! is_string($start)) {
			throw new WorkflowException('$start must be a string');
		}

		if ( empty($end) || ! is_string($end)) {
			throw new WorkflowException('$end must be a string');
		}

		return 'from {'.$start.'} to {'.$end.'}';
	}
	/**
	 * Returns the scenario name for a leave status action.
	 * 
	 * @param string $status the aboslute id of the status that is left
	 * @return string
	 */
	public static function leaveStatus($status = self::ANY_STATUS)
	{
		return 'leave status {'.$status.'}';
	}

	/**
	 * Returns the scenario name for a enter status action.
	 * 
	 * @param string $status the aboslute id of the entered status
	 * @return string
	 */
	public static function enterStatus($status = self::ANY_STATUS)
	{
		return 'enter status {'.$status.'}';
	}
	/**
	 * Returns the scenario name for a enter workflow action.
	 * 
	 * @param string $workflowId the workflow id
	 * @return string
	 */
	public static function enterWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		return 'enter workflow {'.$workflowId.'}';
	}
	/**
	 * Returns the scenario name for a leave workflow action.
	 * 
	 * @param string $workflowId the workflow id
	 * @return string
	 */
	public static function leaveWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		return 'leave workflow {'.$workflowId.'}';
	}

	/**
	 * Test if 2 scenario match.
	 * 
	 * @param string $scenario1
	 * @param string $scenario2
	 * @return boolean TRUE if both scenario names match, FALSE otherwise
	 */
	public static function match($scenario1, $scenario2)
	{
		$match1 = $match2 = [];
		if ( preg_match_all('/([^\\}{]*)\{([^\{\}]+)\}/', $scenario1, $match1, PREG_SET_ORDER) &&
			 preg_match_all('/([^\\}{]*)\{([^\{\}]+)\}/', $scenario2, $match2, PREG_SET_ORDER) ) {

			if ( count($match1) != count($match2) ) {
				return false;
			}
			for ($i = 0; $i < count($match1); $i++) {
				if ( str_replace(' ', '', $match1[$i][1]) != str_replace(' ', '', $match2[$i][1]) ) {
					return false;
				}
				if ( $match1[$i][2] != $match2[$i][2] &&  $match1[$i][2] != '*' && $match2[$i][2] != '*' ) {
					return false;
				}
			}
		} else {
			return false;
		}
		return true;
	}
}
