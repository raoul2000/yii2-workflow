<?php
namespace raoul2000\workflow\source;

/**
 * Interface that must be implemented by WorkflowSource components.
 * 
 * This interface defines basic methods aimed to provide status, workflow and transitions
 * to the SimpleStatusBehavior.
 */
interface IWorkflowSource
{
	/**
	 * Returns the Status instance with id $id.
	 * In case of unexpected error the implementation must return a WorkflowException.
	 *
	 * @see Status
	 * @param mixed $id the status id
	 * @return Status the status instance or NULL if no status could be found for this id.
	 * @throws WorkflowException unexpected error
	 */
	public function getStatus($id, $model = null);
	/**
	 * Returns an array containing all Status instances belonging to the workflow
	 * whose id is passed as argument.
	 * 
	 * @param string $id workflow Id
	 * @return StatusInterface[] list of status. The array key is the status ID
	 * @throws WorkflowException no workflow is found with this Id
	 */
	public function getAllStatuses($id);
	/**
	 * Returns an array of transitions leaving the status whose id is passed as argument.
	 *
	 * If no start status is found a WorkflowException must be thrown.
	 * If not outgoing transition exists for the status, an empty array must be returned.
	 * The array returned must be indexed by ....
	 *
	 * @see Transition
	 * @param mixed $statusId
	 * @return Transition[] an array containing all out going transition from $statusId. If no such
	 * transition exist, this method returns an empty array.
	 * @throws WorkflowException unexpected error
	 */
	public function getTransitions($statusId, $model = null);
	/**
	 * Returns the transitions that leaves status with id $startId and reaches status with id $endId.
	 * 
	 * @param string $startId
	 * @param string $endId
	 * @param unknown $endId
	 */
	public function getTransition($startId, $endId, $model = null);
	/**
	 * Returns the workflow instance whose id is passed as argument.
	 * In case of unexpected error the implementation must return a WorkflowException.
	 *
	 * @see Workflow
	 * @param mixed $id the workflow id
	 * @return Workflow the workflow instance or NULL if no workflow could be found.
	 */
	public function getWorkflow($id);
}
