<?php
namespace raoul2000\workflow\events;

/**
 *
 * Defines the interface that must be implemented by all Event sequence.
 * An <b>event sequence</b> is an array of workflow events that occur on three circumstances
 * for which a method is called :
 * <ul>
 *	<li><b>createEnterWorkflowSequence</b> : when a model enters into a workflow</li>
 *	<li><b>createChangeStatusSequence</b> : when a model status change from a non empty status to another one</li>
 *	<li><b>createLeaveWorkflowSequence</b> : when a model leaves a workflow</li>
 *</ul>
 *
 * For each one of these method, the implementation must returns an array of Workflow events that extend
 * \raoul2000\workflow\events\WorkflowEvent.
 *
 * Two event sequence implementations are provided : {@link \raoul2000\workflow\events\BasicEventSequence} and
 * {@link \raoul2000\workflow\events\ExtendedEventSequence}
 *
 * @see \raoul2000\workflow\events\WorkflowEvent
 *
 */
interface IEventSequence
{
	/**
	 * Creates and returns the sequence of events that occurs when a model enters into a workflow.
	 *
	 * @param \raoul2000\workflow\base\Status $initalStatus the status used to enter into the workflow (the <i>initial status</i>)
	 * @param Object $sender
	 * @return Event[]
	 */
	public function createEnterWorkflowSequence($initalStatus, $sender);
	/**
	 * Creates and returns the sequence of events that occurs when a model leaves a workflow.
	 *
	 * @param \raoul2000\workflow\base\Status $finalStatus the status that the model last visited in the workflow it is leaving
	 * (the <i>final status</i>)
	 * @param Object $sender
	 * @return Event[]
	 */
	public function createLeaveWorkflowSequence($finalStatus, $sender);
	/**
	 * Creates and returns the sequence of events that occurs when a model changes
	 * from an existing status to another existing status.
	 *
	 * @param \raoul2000\workflow\Transition $transition the transition representing the status
	 * change
	 * @param Object $sender
	 * @return Event[]
	 */
	public function createChangeStatusSequence($transition, $sender);
}
