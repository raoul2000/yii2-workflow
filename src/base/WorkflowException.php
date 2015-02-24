<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Exception;

class WorkflowException extends Exception
{
	/**
	 *
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'Workflow Exception';
	}
}
