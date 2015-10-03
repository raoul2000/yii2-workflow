<?php
namespace raoul2000\workflow\events;

use yii\base\Object;
use raoul2000\workflow\events\IEventSequence;

/**
 * This event sequence provider include additional generic events to each sequence.
 *
 * For example, when entering into a workflow, the generic event EnterWorkflow() is
 * added to the sequence allowing the developer to create a handler invoked each
 * time a model enters into a workflow.
 *
 * @see \raoul2000\workflow\events\IEventSequence
 */
class ExtendedEventSequence extends Object implements IEventSequence
{
	/**
	 * Produces the following event sequence when a model enters a workflow.
	 *
	 * - beforeEnterWorkflow(*)
	 * - beforeEnterWorkflow(WID)
	 * - beforeEnterStatus(*)
	 * - beforeEnterStatus(ID)
	 *
	 * - afterEnterWorkflow(*)
	 * - afterEnterWorkflow(WID)
	 * - afterEnterStatus(*)
	 * - afterEnterStatus(ID)
	 *
	 * Where WID is the workflow Id and ID is the status Id.
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

			////////// BEFORE //////////////////////////////////////////////////////////////

			'before' => [

				new WorkflowEvent(
					WorkflowEvent::beforeEnterWorkflow(),
					$config
					),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterWorkflow($initalStatus->getWorkflowId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus($initalStatus->getId()),
					$config
				)
			],

			////////// AFTER  //////////////////////////////////////////////////////////////

			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterEnterWorkflow(),
					$config
					),
				new WorkflowEvent(
					WorkflowEvent::afterEnterWorkflow($initalStatus->getWorkflowId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus(),
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
	 * Produces the following event sequence when a model leaves a workflow.
	 *
	 * - beforeLeaveStatus(*)
	 * - beforeLeaveStatus(ID)
	 * - beforeLeaveWorkflow(*)
	 * - beforeLeaveWorkflow(WID)
	 *
	 * - afterLeaveStatus(*)
	 * - afterLeaveStatus(ID)
	 * - afterLeaveWorkflow(*)
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

			////////// BEFORE //////////////////////////////////////////////////////////////

			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus($finalStatus->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveWorkflow(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveWorkflow($finalStatus->getWorkflowId()),
					$config
				)
			],

			////////// AFTER  //////////////////////////////////////////////////////////////

			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus($finalStatus->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveWorkflow(),
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
	 * Produces the following event sequence when a model changes from status A to status B.
	 *
	 * - beforeLeaveStatus(*)
	 * - beforeLeaveStatus(A)
	 * - beforeChangeStatusFrom(A)to(B)
	 * - beforeEnterStatus(*)
	 * - beforeEnterStatus(B)
	 *
	 * - afterLeaveStatus(*)
	 * - afterLeaveStatus(A)
	 * - afterChangeStatusFrom(A)to(B)
	 * - afterEnterStatus(*)
	 * - afterEnterStatus(B)

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

			////////// BEFORE //////////////////////////////////////////////////////////////

			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus($transition->getStartStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus($transition->getEndStatus()->getId()),
					$config
				)
			],

			////////// AFTER //////////////////////////////////////////////////////////////

			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus(),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus($transition->getStartStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					$config
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus(),
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
