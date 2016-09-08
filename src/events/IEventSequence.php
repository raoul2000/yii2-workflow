<?php
namespace raoul2000\workflow\events;

/**
 *
 * Defines the interface that must be implemented by all Event sequence.
 * An <b>event sequence</b> is an array of workflow events that occurs on three occasions :
 * <ul>
 *	<li><b>createEnterWorkflowSequence</b> : when a model enters into a workflow</li>
 *	<li><b>createChangeStatusSequence</b> : when a model status change from a non empty status to another one</li>
 *	<li><b>createLeaveWorkflowSequence</b> : when a model leaves a workflow</li>
 *</ul>
 *
 * For each one of these methods, the implementation must returns with 2 keys : **before** and **after**. For each
 * key, the value is an array of `\raoul2000\workflow\events\WorkflowEvent` representing the sequence of event
 * to fire *before* or *after* the workflow event occurs.
 *
 * Two event sequences implementations are provided :
 *
 * - {@link \raoul2000\workflow\events\BasicEventSequence}
 * - {@link \raoul2000\workflow\events\ExtendedEventSequence}
 */
interface IEventSequence
{
	/**
	 * Creates and returns the sequence of events that occurs when a model enters into a workflow.
	 *
	 * @param \raoul2000\workflow\base\StatusInterface $initalStatus the status used to enter into the workflow (the <i>initial status</i>)
	 * @param Object $sender
	 * @return array
	 */
	public function createEnterWorkflowSequence($initalStatus, $sender);
	/**
	 * Creates and returns the sequence of events that occurs when a model leaves a workflow.
	 *
	 * @param \raoul2000\workflow\base\StatusInterface $finalStatus the status that the model last visited in the workflow it is leaving
	 * (the <i>final status</i>)
	 * @param Object $sender
	 * @return array
	 */
	public function createLeaveWorkflowSequence($finalStatus, $sender);
	/**
	 * Creates and returns the sequence of events that occurs when a model changes
	 * from an existing status to another existing status.
	 *
	 * @param \raoul2000\workflow\base\TransitionInterface $transition the transition representing the status
	 * change
	 * @param Object $sender
	 * @return array
	 */
	public function createChangeStatusSequence($transition, $sender);
}
