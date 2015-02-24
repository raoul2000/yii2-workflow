<?php
namespace raoul2000\workflow\validation;

use raoul2000\workflow\base\WorkflowException;

class WorkflowScenario
{
	const ANY_STATUS = '*';
	const ANY_WORKFLOW = '*';

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
	public static function leaveStatus($status = self::ANY_STATUS)
	{
		return 'leave status {'.$status.'}';
	}

	public static function enterStatus($status = self::ANY_STATUS)
	{
		return 'enter status {'.$status.'}';
	}
	public static function enterWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		return 'enter workflow {'.$workflowId.'}';
	}
	public static function leaveWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		return 'leave workflow {'.$workflowId.'}';
	}

	/**
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
