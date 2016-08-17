<?php
namespace raoul2000\workflow\helpers;

use yii\db\BaseActiveRecord;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;

/**
 * Helper class for yii2-workflow.
 *
 */
class WorkflowHelper
{
	/**
	 * Returns an associative array containing all statuses that can be reached by model.
	 * 
	 * 
	 * @param BaseActiveRecord $model
	 * @param boolean $validate when TRUE only those status with successfull attribute validation are included. When FALSE (default)
	 * Attribute validation is done performed.
	 * @param boolean $beforeEvents when TRUE all configured *before* events are fired : only the status that don't invalidate the
	 * workflow event are included in the returned array, otherwise no event is fired and all next status are included 
	 * @param boolean $includeCurrent when TRUE the current model status is added to the returned array. When FALSE (default)
	 * only next statuses are included
	 * @throws WorkflowException
	 * @return array
	 */
	public static function getNextStatusListData($model, $validate = false, $beforeEvents = false, $includeCurrent = false)
	{
		if (! SimpleWorkflowBehavior::isAttachedTo($model)) {
			throw new WorkflowException('The model does not have a SimpleWorkflowBehavior behavior');
		}
		$listData = [];

		if( $includeCurrent ) {
			$currentStatus = $model->getWorkflowStatus();
			$listData[$currentStatus->getId()] = $currentStatus->getLabel();
		}
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
	 * @param IWorkflowSource $workflowSource
	 * @return array
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

	/**
	 * Returns the items and options for a dropDownList
	 * All status options are in the list, but invalid transitions are disabled
	 *
	 * Example:
	 * $statusDropDownData = WorkflowHelper::getStatusDropDownData($model);
	 * // Html
	 * echo Html::dropDownList('status', $model->status, $statusDropdown['items'], ['options' => $statusDropdown['options']]);
	 * // ActiveForm
	 * echo $form->field($model, 'status')->dropDownList($statusDropDownData['items'], ['options' => $statusDropDownData['options']]);
	 * 
	 * @param BaseActiveRecord|SimpleWorkflowBehavior $model
	 * @return array
	 */
	public static function getStatusDropDownData($model)
	{
		$transitions = array_keys($model->getWorkflowSource()->getTransitions($model->getWorkflowStatus()->getId()));
		$items = WorkflowHelper::getAllStatusListData($model->getWorkflow()->getId(), $model->getWorkflowSource());
		$options = [];
		foreach (array_keys($items) as $status) {
			if ($status != $model->getWorkflowStatus()->getId() && !in_array($status, $transitions)) {
				$options[$status]['disabled'] = true;
			}
		}
		return [
			'items' => $items,
			'options' => $options,
		];
	}

	/**
	 * Returns the status string of the next valid status from the list of transitions
	 *
	 * @param BaseActiveRecord|SimpleWorkflowBehavior $model
	 * @return string
	 */
	public static function getNextStatus($model)
	{
		$currentStatus = $model->getAttribute('status');
		$statusList = $model->getWorkflowSource()->getAllStatuses($model->getWorkflow()->getId());
		$transitions = array_keys(WorkflowHelper::getNextStatusListData($this->owner));
		$started = false;
		foreach ($statusList as $status) {
			$status_id = $status->getId();
			if ($started) {
				if (in_array($status_id, $transitions) && static::isValidNextStatus($model, $status_id)) {
					return $status_id;
				}
			}
			if ($status_id == $currentStatus) {
				$started = true;
			}
		}
		return $currentStatus;
	}

	/**
	 * Checks if a given status is a valid transition from the current status
	 *
	 * @param BaseActiveRecord|SimpleWorkflowBehavior $model
	 * @param string $status_id
	 * @return bool
	 */
	public static function isValidNextStatus($model, $status_id)
	{
		$eventSequence = $model->getEventSequence($status_id);
		foreach ($eventSequence['before'] as $event) {
			if ($model->hasEventHandlers($event->name)) {
				$model->trigger($event->name, $event);
				if ($event->isValid === false) {
					return false;
				}
			}
		}
		return true;
	}
}
