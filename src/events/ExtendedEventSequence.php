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
	 * @see \raoul2000\workflow\events\IEventSequenceScheme::createEnterWorkflowSequence()
	 */
	public function createEnterWorkflowSequence($initalStatus, $sender)
	{
		return [

			////////// BEFORE //////////////////////////////////////////////////////////////

			'before' => [

				new WorkflowEvent(
					WorkflowEvent::beforeEnterWorkflow(),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
					),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterWorkflow($initalStatus->getWorkflowId()),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus(),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus($initalStatus->getId()),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
				)
			],

			////////// AFTER  //////////////////////////////////////////////////////////////

			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterEnterWorkflow(),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
					),
				new WorkflowEvent(
					WorkflowEvent::afterEnterWorkflow($initalStatus->getWorkflowId()),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus(),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus($initalStatus->getId()),
					[
						'end'        => $initalStatus,
						'sender'  	 => $sender
					]
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
	 * @see \raoul2000\workflow\events\IEventSequenceScheme::createLeaveWorkflowSequence()
	 */
	public function createLeaveWorkflowSequence($finalStatus, $sender)
	{
		return [

			////////// BEFORE //////////////////////////////////////////////////////////////

			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus(),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus($finalStatus->getId()),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveWorkflow(),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveWorkflow($finalStatus->getWorkflowId()),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				)
			],

			////////// AFTER  //////////////////////////////////////////////////////////////

			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus(),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus($finalStatus->getId()),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveWorkflow(),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveWorkflow($finalStatus->getWorkflowId()),
					[
						'start'      => $finalStatus,
						'sender'  	 => $sender
					]
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

	 * @see \raoul2000\workflow\events\IEventSequenceScheme::createChangeStatusSequence()
	 */
	public function createChangeStatusSequence($transition, $sender)
	{
		return [

			////////// BEFORE //////////////////////////////////////////////////////////////

			'before' => [
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus(),
					[
						'start'      => $transition->getStartStatus(),
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeLeaveStatus($transition->getStartStatus()->getId()),
					[
						'start'      => $transition->getStartStatus(),
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					[
						'start'      => $transition->getStartStatus(),
						'end'  		 => $transition->getEndStatus(),
						'transition' => $transition,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus(),
					[
						'end'  		 => $transition->getEndStatus(),
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::beforeEnterStatus($transition->getEndStatus()->getId()),
					[
						'end'  		 => $transition->getEndStatus(),
						'sender'  	 => $sender
					]
				)
			],

			////////// AFTER //////////////////////////////////////////////////////////////

			'after' => [
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus(),
					[
						'start'      => $transition->getStartStatus(),
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterLeaveStatus($transition->getStartStatus()->getId()),
					[
						'start'      => $transition->getStartStatus(),
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterChangeStatus($transition->getStartStatus()->getId(), $transition->getEndStatus()->getId()),
					[
						'start'      => $transition->getStartStatus(),
						'end'  		 => $transition->getEndStatus(),
						'transition' => $transition,
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus(),
					[
						'end'  		 => $transition->getEndStatus(),
						'sender'  	 => $sender
					]
				),
				new WorkflowEvent(
					WorkflowEvent::afterEnterStatus($transition->getEndStatus()->getId()),
					[
						'end'  		 => $transition->getEndStatus(),
						'sender'  	 => $sender
					]
				)
			]
		];
	}
}
