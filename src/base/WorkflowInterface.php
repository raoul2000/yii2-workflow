<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Interface implemented by Workflow objects.
 */
interface WorkflowInterface
{
	/**
	 * Returns the id of this workflow
	 * 
	 * @return string the workflow id
	 */
	public function getId();
	/**
	 * Returns the id of the initial status for this workflow.
	 *
	 * @return string status id
	 */
	public function getInitialStatusId();
	
	/**
	 * Returns the initial status instance for this workflow.
	 * 
	 * @return \raoul2000\workflow\base\StatusInterface the initial status instance
	 * @throws \raoul2000\workflow\base\WorkflowException when no source component is available
	 */
	public function getInitialStatus();
	/**
	 * Returns an array containing all Status instances belonging to this workflow.
	 * 
	 * @return \raoul2000\workflow\base\StatusInterface[]  status list belonging to this workflow
	 * @throws \raoul2000\workflow\base\WorkflowException when no source component is available
	 */
	public function getAllStatuses();	
}
