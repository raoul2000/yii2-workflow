<?php
namespace raoul2000\workflow;

use yii\db\BaseActiveRecord;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;

class WorkflowHelper
{
	/**
	 *
	 * @param BaseActiveRecord $model
	 * @param boolean $validate
	 * @param boolean $beforeEvents
	 * @throws WorkflowException
	 * @return array
	 */
	public static function getNextStatusListData($model, $validate = false, $beforeEvents = false)
	{
		if (! SimpleWorkflowBehavior::isAttachedTo($model)) {
			throw new WorkflowException('The model does not have a SimpleWorkflowBehavior behavior');
		}
		$listData = [];
		$report = $model->getNextStatuses($validate, $beforeEvents);
		foreach ($report as $endStatusId => $info) {
			if (! isset($info['isValid']) || $info['isValid'] === true) {
				$listData[$endStatusId] = $info['status']->getLabel();
			}
		}
		return $listData;
	}
}
