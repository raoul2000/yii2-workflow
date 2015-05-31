<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * Transition object is an oriented link between a start and an end status.
 */
class Transition extends WorkflowBaseObject implements TransitionInterface
{
	/**
	 * @var Status the status this transition is starting from
	 */
	private $_startStatus;
	/**
	 * @var Status the status this transition is ending to.
	 */
	private $_endStatus;
	private $_id = null;
	/**
	 * Creates a Transition object.
	 *
	 * To create a new Transition, you should provide following mandatory values in the
	 * configuration array $config :
	 *
	 * - **start** : the start Status instance
	 * - **end** : the end Status instance
	 *
	 * @see Status
	 * @param array $config
	 * @throws InvalidConfigException
	 */
	public function __construct($config = [])
	{
		if ( ! empty($config['start'])) {
			$this->_startStatus = $config['start'];
			unset($config['start']);
			if ( ! $this->_startStatus instanceof StatusInterface ) {
				throw new WorkflowException('Start status object must implement raoul2000\workflow\base\StatusInterface');
			}
		} else {
			throw new InvalidConfigException('missing start status');
		}

		if ( ! empty($config['end'])) {
			$this->_endStatus = $config['end'];
			unset($config['end']);
			if ( ! $this->_endStatus instanceof Status) {
				throw new WorkflowException('End status object must implement raoul2000\workflow\base\StatusInterface');
			}

		} else {
			throw new InvalidConfigException('missing end status');
		}
		parent::__construct($config);
		$this->_id = $this->_startStatus->getId().'-'.$this->_endStatus->getId();
	}
	/**
	 * Returns the id of this transition.
	 *
	 * The id is built by concatenating the start and the end status Ids, separated with character '-'. For instance, a transition
	 * between status A and B has an idea equals to "A-B".
	 *
	 * @return string the transition Id
	 * @see \raoul2000\workflow\base\WorkflowBaseObject::getId()
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @see \raoul2000\workflow\base\TransitionInterface::getEndStatus()
	 */
	public function getEndStatus()
	{
		return $this->_endStatus;
	}
	/**
	 * @see \raoul2000\workflow\base\TransitionInterface::getStartStatus()
	 */
	public function getStartStatus()
	{
		return $this->_startStatus;
	}
}
