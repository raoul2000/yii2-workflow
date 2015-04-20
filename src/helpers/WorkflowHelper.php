<?php
namespace raoul2000\workflow\helpers;

use yii\db\BaseActiveRecord;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;

class WorkflowHelper
{
	/**
	 * Returns an associative array containing all statuses that can be reached by model.
	 * 
	 * Note that the current model status is NOT included in this list.
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
	/**
	 * Returns an associative array containing all statuses that belong to a workflow.
	 * The array returned is suitable to be used as list data value in (for instance) a dropdown list control.
	 * 
	 * Usage example : assuming model Post has a SimpleWorkflowBehavior the following code displays a dropdown list
	 * containing all statuses defined in $post current the workflow : 
	 * 
	 * echo Html::dropDownList(
	 * 		'status',
	 * 		null,
	 * 		WorkflowHelper::getAllStatusListData(
	 * 			$post->getWorkflow()->getId(),
	 * 			$post->getWorkflowSource()
	 * 		)
	 * )
	 * 
	 * @param string $workflowId
	 * @param Object $workflowSource
	 * @return Array
	 */
	public static function getAllStatusListData($workflowId, $workflowSource)
	{
		$listData = [];
		$statuses = $workflowSource->getAllStatuses($workflowId);
		foreach ($statuses as $statusId => $statusInstance) {
			$listData[$statusId] =$statusInstance->getLabel();
		}
		return $listData;
	}
	
	/**
	 * Displays the status for the model passed as argument.
	 * 
	 * This method assumes that the status includes a metadata value called 'labelTemplate' that contains
	 * the HTML template of the rendering status. In this template the string '{label}' will be replaced by the 
	 * status label.
	 * 
	 * Example : 
	 *		'status' => [
	 *			'draft' => [
	 *				'label' => 'Draft',
	 *				'transition' => ['ready' ],
	 *				'metadata' => [
	 *					'labelTemplate' => '<span class="label label-default">{label}</span>'
	 *				]
	 *			],
	 * 
	 * @param BaseActiveRecord $model
	 * @return string|NULL the HTML rendered status or null if not labelTemplate is found
	 */
	public static function renderLabel($model)
	{
		if($model->hasWorkflowStatus()) {
			$labelTemplate = $model->getWorkflowStatus()->getMetadata('labelTemplate');
			if( ! empty($labelTemplate)) {
				return strtr($labelTemplate, ['{label}' => $model->getWorkflowStatus()->getLabel()]);
			}
		}
		return null;
	}
}
