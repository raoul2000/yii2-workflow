<?php
namespace raoul2000\workflow\base;

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
	 * @return Transition[] the list of out-going transitions for this status. Note that an empty array can be returned if this
	 * status has no out-going transition (i.e. no other status can be reached).
	 */
	public function getTransitions();
}
