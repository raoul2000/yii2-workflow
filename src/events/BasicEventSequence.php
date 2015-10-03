<?php
namespace raoul2000\workflow\events;

use yii\base\Object;
use raoul2000\workflow\events\IEventSequence;

/**
 * The basic event sequence.
 *
 * @see IEventSequence
 */
class BasicEventSequence extends Object implements IEventSequence
{
	/**
	 * Produces the following sequence when a model enters a workflow :
	 *
	 * - beforeEnterWorkflow(workflowID)
	 * - beforeEnterStatus(statusID)
	 *
	 * - afterEnterWorkflow(workflowID)
	 * - afterEnterStatus(statusID)
	 *
	 * @see IEventSequence::createEnterWorkflowSequence()
	 */
	public function createEnterWorkflowSequence($initalStatus, $sender)
	{
		$config = [
			'end'        => $initalStatus,
			'sender'  	 => $sender
		];
		return [
			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeEnterWorkflow($initalStatus->getWorkflowId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus($initalStatus->getId()),
					$config
				)
			],
			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterEnterWorkflow($initalStatus->getWorkflowId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus($initalStatus->getId()),
					$config
				)
			]
		];
	}

	/**
	 * Produces the following sequence when a model leaves a workflow :
	 *
	 * - beforeLeaveStatus(statusID)
	 * - beforeLeaveWorkflow(workflowID)
	 *
	 * - afterLeaveStatus(statusID)
	 * - afterLeaveWorkflow(workflowID)
	 *
	 * @see IEventSequence::createLeaveWorkflowSequence()
	 */
	public function createLeaveWorkflowSequence($finalStatus, $sender)
	{
		$config = [
			'start'      => $finalStatus,
			'sender'  	 => $sender
		];
		
		return [
			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus($finalStatus->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveWorkflow($finalStatus->getWorkflowId()),
					$config
				)
			],
			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus($finalStatus->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveWorkflow($finalStatus->getWorkflowId()),
					$config
				)
			]
		];
	}

	/**
	 * Produces the following sequence when a model changes from status A to status B:
	 *
	 * - beforeLeaveStatus(A)
	 * - beforeChangeStatus(A,B)
	 * - beforeEnterStatus(B)
	 *
	 * - afterLeaveStatus(A)
	 * - afterChangeStatus(A,B)
	 * - afterEnterStatus(B)
	 *
	 * @see IEventSequence::createChangeStatusSequence()
	 */
	public function createChangeStatusSequence($transition, $sender)
	{
		$config = [
			'start'      => $transition->getStartStatus(),
			'end'  		 => $transition->getEndStatus(),
			'transition' => $transition,
			'sender'  	 => $sender			
		];
		return [
			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus($transition->getStartStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus($transition->getEndStatus()->getId()),
					$config
				)
			],
			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus($transition->getStartStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus($transition->getEndStatus()->getId()),
					$config
				)
			]
		];
	}
}
