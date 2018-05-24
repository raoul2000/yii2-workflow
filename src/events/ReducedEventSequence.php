<?php
namespace raoul2000\workflow\events;

use yii\base\BaseObject;
use raoul2000\workflow\events\IEventSequence;

/**
 * The reduced event sequence.
 *
 * @see \raoul2000\workflow\events\IEventSequence
 *
 */
class ReducedEventSequence extends BaseObject implements IEventSequence
{
	/**
	 * Produces the following sequence when a model enters a workflow :
	 *
	 * - beforeEnterWorkflow(WID)
	 * - afterEnterWorkflow(WID)
	 *
	 * @see \raoul2000\workflow\events\IEventSequence::createEnterWorkflowSequence()
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
			],
			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterEnterWorkflow($initalStatus->getWorkflowId()),
					$config
				),
			]
		];
	}

	/**
	 * Produces the following sequence when a model leaves a workflow :
	 *
	 * - beforeLeaveWorkflow(WID)
	 * - afterLeaveWorkflow(WID)
	 *
	 * @see \raoul2000\workflow\events\IEventSequence::createLeaveWorkflowSequence()
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
					WorkflowEvent::beforeLeaveWorkflow($finalStatus->getWorkflowId()),
					$config
				)
			],
			'after' => [
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
	 * - beforeChangeStatus(A,B)
	 * - afterChangeStatus(A,B)
	 *
	 * @see \raoul2000\workflow\events\IEventSequence::createChangeStatusSequence()
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
					WorkflowEvent::beforeChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					$config
				)
			],
			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					$config
				)
			]
		];
	}
}
