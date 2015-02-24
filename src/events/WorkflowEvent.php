<?php
namespace raoul2000\workflow\events;

use yii\base\ModelEvent;
use raoul2000\workflow\base\WorkflowException;


/**
 * WorkflowEvent is the base class for all event fired within a workflow.
 */
class WorkflowEvent extends ModelEvent
{
	const ANY_STATUS = '*';
	const ANY_WORKFLOW = '*';

	private $_start;
	private $_end;
	private $_transition;

	private $_errorMessage = [];

	/**
	 * Create a WorkflowEvent instance.
	 *
	 * @param $name string name of the event.
	 * @see \yii\base\Object::__construct()
	 */
	public function __construct($name, array $config = [])
	{
		if (empty($name)) {
			throw new WorkflowException('Failed to create event instance : missing $name value');
		} else {
			$this->name = $name;
		}
		if ( isset($config['start'])) {
			$this->_start = $config['start'];
			unset($config['start']);
		}
		if ( isset($config['end'])) {
			$this->_end = $config['end'];
			unset($config['end']);
		}
		if ( isset($config['transition'])) {
			$this->_transition = $config['transition'];
			unset($config['transition']);
		}
		unset($config['name']);
		parent::__construct($config);
	}
	/**
	 * @return \raoul2000\workflow\base\Status the start status involved in this event
	 */
	public function getStartStatus()
	{
		return $this->_start;
	}
	/**
	 * @return \raoul2000\workflow\base\Status the end status involved in this event
	 */
	public function getEndStatus()
	{
		return $this->_end;
	}
	/**
	 * @return \raoul2000\workflow\Transition the transition involved in this event or NULL if no
	 * transition is available (e.g. EnterWorkflow, LeaveWorkflow)
	 * @see \raoul2000\workflow\Transition
	 */
	public function getTransition()
	{
		return $this->_transition;
	}

	/**
	 * Invalidate this event.
	 * Calling this methid is equivalent to setting the *isValid* property to false. Additionnally an
	 * message can be added to the internal error message queue.
	 * @param string $message
	 */
	public function invalidate($message = null)
	{
		$this->isValid = false;
		if ( !empty($message)) {
			$this->_errorMessage[] = $message;
		}
	}
	/**
	 * Returns an array containg all error messages.
	 * An error message can be set when calling the *invalidate()* method.
	 *
	 * @return string[] the list of error messages
	 */
	public function getErrors()
	{
		return $this->_errorMessage;
	}
	///////// CHANGE STATUS /////////////////////////////////////////////////////

	/**
	 *
	 * @param string $start ID of the status which is at the start of the transition (the status that is left)
	 * @param string $end ID of the status which is at the end of the transition (the status that is reached)
	 * @return string name of the event
	 */
	public static function beforeChangeStatus($start, $end)
	{
		self::_checkNonEmptyString('start', $start);
		self::_checkNonEmptyString('end', $end);

		return 'beforeChangeStatusFrom{'.$start.'}to{'.$end.'}';
	}

	/**
	 *
	 * @param string $start ID of the status which is at the start of the transition (the status that is left)
	 * @param string $end ID of the status which is at the end of the transition (the status that is reached)
	 * @return string name of the event
	 */
	public static function afterChangeStatus($start, $end)
	{
		self::_checkNonEmptyString('start', $start);
		self::_checkNonEmptyString('end', $end);

		return 'afterChangeStatusFrom{'.$start.'}to{'.$end.'}';
	}

	///////// LEAVE STATUS /////////////////////////////////////////////////////

	public static function beforeLeaveStatus($status = self::ANY_STATUS)
	{
		self::_checkNonEmptyString('status', $status);
		return 'beforeLeaveStatus{'.$status.'}';
	}
	public static function afterLeaveStatus($status = self::ANY_STATUS)
	{
		self::_checkNonEmptyString('status', $status);
		return 'afterLeaveStatus{'.$status.'}';
	}

	///////// ENTER STATUS /////////////////////////////////////////////////////

	public static function beforeEnterStatus($status = self::ANY_STATUS)
	{
		self::_checkNonEmptyString('status', $status);
		return 'beforeEnterStatus{'.$status.'}';
	}
	public static function afterEnterStatus($status = self::ANY_STATUS)
	{
		self::_checkNonEmptyString('status', $status);
		return 'afterEnterStatus{'.$status.'}';
	}

	///////// ENTER WORKFLOW /////////////////////////////////////////////////////

	public static function beforeEnterWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		self::_checkNonEmptyString('workflowId', $workflowId);
		return 'beforeEnterWorkflow{'.$workflowId.'}';
	}
	public static function afterEnterWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		self::_checkNonEmptyString('workflowId', $workflowId);
		return 'afterEnterWorkflow{'.$workflowId.'}';
	}

	///////// LEAVE WORKFLOW /////////////////////////////////////////////////////

	public static function beforeLeaveWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		self::_checkNonEmptyString('workflowId', $workflowId);
		return 'beforeLeaveWorkflow{'.$workflowId.'}';
	}
	public static function afterLeaveWorkflow($workflowId = self::ANY_WORKFLOW)
	{
		self::_checkNonEmptyString('workflowId', $workflowId);
		return 'afterLeaveWorkflow{'.$workflowId.'}';
	}

	private static function _checkNonEmptyString($argName, $argValue)
	{
		if ( empty($argValue) || ! is_string($argValue)) {
			throw new WorkflowException("argument '$argName' must be a string");
		}
	}
}
