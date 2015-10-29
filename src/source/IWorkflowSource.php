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
	 * @param mixed $id the status id
	 * @return \raoul2000\workflow\base\StatusInterface the status instance or NULL if no status could be found for this id.
	 * @throws \raoul2000\workflow\base\WorkflowException unexpected error
	 */
	public function getStatus($id, $model = null);
	/**
	 * Returns an array containing all Status instances belonging to the workflow
	 * whose id is passed as argument.
	 * 
	 * @param string $id workflow Id
	 * @return \raoul2000\workflow\base\StatusInterface[] list of status. The array key is the status ID
	 * @throws \raoul2000\workflow\base\WorkflowException no workflow is found with this Id
	 */
	public function getAllStatuses($id);
	/**
	 * Returns an array of transitions leaving the status whose id is passed as argument.
	 *
	 * If no start status is found a WorkflowException must be thrown.
	 * If not outgoing transition exists for the status, an empty array must be returned.
	 * The array returned must be indexed by ....
	 *
	 * @param mixed $statusId
	 * @return \raoul2000\workflow\base\TransitionInterface[] an array containing all out going transition from $statusId. If no such
	 * transition exist, this method returns an empty array.
	 * @throws \raoul2000\workflow\base\WorkflowException unexpected error
	 */
	public function getTransitions($statusId, $model = null);
	/**
	 * Returns the transitions that leaves status with id $startId and reaches status with id $endId.
	 * 
	 * @param string $startId
	 * @param string $endId
	 * @param mixed $model
	 *  @return \raoul2000\workflow\base\TransitionInterface the transition between start and end status
	 */
	public function getTransition($startId, $endId, $model = null);
	/**
	 * Returns the workflow instance whose id is passed as argument.
	 * In case of unexpected error the implementation must return a WorkflowException.
	 *
	 * @param mixed $id the workflow id
	 * @return \raoul2000\workflow\base\WorkflowInterface the workflow instance or NULL if no workflow could be found.
	 */
	public function getWorkflow($id);
}
