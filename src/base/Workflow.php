<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;
/**
 * Implements the Workflow as an object having an `$id` and an `initialStatus`.
 * The Workflow class inherits from [[WorkflowBaseObject]] that provides support for **metadata** attributes.
 * 
 * @see WorkflowBaseObject
 */
class Workflow extends WorkflowBaseObject implements WorkflowInterface
{
	const PARAM_INITIAL_STATUS_ID = 'initialStatusId';

	private $_id;
	private $_initialStatusId;

	/**
	 * Workflow constructor.
	 * 
	 * @param array $config
	 * @throws InvalidConfigException
	 */
	public function __construct($config = [])
	{
		if ( ! empty($config['id'])) {
			$this->_id = $config['id'];
			unset($config['id']);
		} else {
			throw new InvalidConfigException('missing workflow id ');
		}

		if ( ! empty($config[self::PARAM_INITIAL_STATUS_ID])) {
			$this->_initialStatusId = $config[self::PARAM_INITIAL_STATUS_ID];
			unset($config[self::PARAM_INITIAL_STATUS_ID]);
		} else {
			throw new InvalidConfigException('missing initial status id');
		}
		parent::__construct($config);
	}
	/**
	 * @see \raoul2000\workflow\base\WorkflowBaseObject::getId()
	 */
	public function getId()
	{
		return $this->_id;
	}
	/**
	 * @see \raoul2000\workflow\base\WorkflowInterface::getInitialStatusId()
	 */
	public function getInitialStatusId()
	{
		return $this->_initialStatusId;
	}
	
	/**
	 * @see \raoul2000\workflow\base\WorkflowInterface::getInitialStatus()
	 */
	public function getInitialStatus() {
		if( $this->getSource() === null) {
			throw new WorkflowException('no workflow source component available');
		}
		return $this->getSource()->getStatus($this->getInitialStatusId());		
	}

}
