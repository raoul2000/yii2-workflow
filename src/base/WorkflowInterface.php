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
	 */
	public function getInitialStatus();
}
