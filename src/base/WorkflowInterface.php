<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * 
 */
interface WorkflowInterface
{
	/**
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
	 * Returns the initial status instance for this workflow
	 * @return StatusInterface the initial status instance
	 * @throws raoul2000\workflow\base\WorkflowException when no source component is available
	 */
	public function getInitialStatus();
	/**
	 * Returns an array containing all Status instances belonging to this workflow.
	 * @return Status[]  status list belonging to this workflow
	 * @throws raoul2000\workflow\base\WorkflowException when no source component is available
	 */
	public function getAllStatuses();	
}
