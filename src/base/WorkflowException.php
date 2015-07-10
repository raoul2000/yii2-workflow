<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Exception;

/**
 * WorkflowException represents a generic workflow exception for various purposes.
 */
class WorkflowException extends Exception
{
	/**
	 * get the name of this exception
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'Workflow Exception';
	}
}
