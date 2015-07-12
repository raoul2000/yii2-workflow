<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * A Status object is a component of a workflow.
 *
 * @author Raoul
 */
class Status extends WorkflowBaseObject implements StatusInterface
{
	/**
	 * @var string the status Id
	 */
	private $_id;
	/**
	 * @var string the status label
	 */
	private $_label = '';
	/**
	 * @var string the workflow Id
	 */
	private $_workflow_id;
	/**
	 * @var Transition[] list of all out-going transitions for this status
	 */
	private $_transitions = [];
	
	/**
	 * Status constructor.
	 *
	 * To create a Status you must provide following values
	 * in the $config array passed as argument:
	 *
	 * - **id** : the id for this status.
	 * - **workflowId ** : the id of the workflow this status belongs to.
	 *
	 * Following values are optional :
	 *
	 * - **label** : human readable name for this status.
	 *
	 * @param array $config
	 * @throws InvalidConfigException
	 */
	public function __construct($config = [])
	{
		if ( ! empty($config['workflowId'])) {
			$this->_workflow_id = $config['workflowId'];
			unset($config['workflowId']);
		} else {
			throw new InvalidConfigException('missing workflow id');
		}

		if ( ! empty($config['id'])) {
			$this->_id = $config['id'];
			unset($config['id']);
		} else {
			throw new InvalidConfigException('missing status id');
		}

		if ( ! empty($config['label'])) {
			$this->_label = $config['label'];
			unset($config['label']);
		}
		parent::__construct($config);
	}
	/**
	 * Returns the id of this status.
	 *
	 * Note that the status id returned must be unique inside the workflow it belongs to, but it
	 * doesn't have to be unique among all workflows.
	 * 
	 * @return string the id for this status
	 */
	public function getId()
	{
		return $this->_id;
	}
	/**
	 * Returns the label for this status.
	 *
	 * @return string the label for this status. .
	 */
	public function getLabel()
	{
		return $this->_label;
	}
	/**
	 * @return string the id of the workflow this status belongs to.
	 */
	public function getWorkflowId()
	{
		return $this->_workflow_id;
	}
	/**
	 * 
	 * @see \raoul2000\workflow\base\StatusInterface::getTransitions()
	 */
	public function getTransitions()
	{
		if( $this->getSource() === null) {
			throw new WorkflowException('no workflow source component available');
		}
		return $this->getSource()->getTransitions($this->getId());
	}
	/**
	 * @see \raoul2000\workflow\base\StatusInterface::getWorkflow()
	 */
	public function getWorkflow()
	{
		if( $this->getSource() === null) {
			throw new WorkflowException('no workflow source component available');
		}
		return $this->getSource()->getWorkflow($this->getWorkflowId());		
	}
	/**
	 * @see \raoul2000\workflow\base\StatusInterface::isInitialStatus()
	 */	
	public function isInitialStatus()
	{
		if( $this->getSource() === null) {
			throw new WorkflowException('no workflow source component available');
		}
		return $this->getWorkflow()->getInitialStatusId() == $this->getId();		
	}
}
