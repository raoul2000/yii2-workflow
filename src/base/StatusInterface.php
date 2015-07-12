<?php
namespace raoul2000\workflow\base;

/**
 * This interface must be implemented by Status objects.
 */
interface StatusInterface
{
	/**
	 * Returns the id of this status.
	 *
	 * @return string the id for this status
	 */
	public function getId();

	/**
	 * Returns the label for this status.
	 *
	 * @return string the label for this status. .
	 */
	public function getLabel();
	/**
	 * @return string the id of the workflow this status belongs to.
	 */
	public function getWorkflowId();
	/**
	 * Returns the list of Transitions instances leaving this status.
	 * 
	 * The array returned is indexed by the canonical id of the end status. Note that an empty array can be returned if this
	 * status has no out-going transition (i.e. no other status can be reached).
	 * 
	 * @return Transition[] the list of out-going transitions for this status. 
	 */
	public function getTransitions();
	/**
	 * Returns the workflow instance this status belongs to
	 * @return Workflow the workflow instance
	 */
	public function getWorkflow();
	/**
	 * Test is this status is the initial status of the parent workflow
	 * @return bool TRUE if this status is the initial status of its parent workflow, FALSE otherwise
	 */
	public function isInitialStatus();
}
