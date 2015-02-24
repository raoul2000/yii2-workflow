<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

class Workflow extends WorkflowBaseObject
{
	const PARAM_INITIAL_STATUS_ID = 'initialStatusId';

	private $_id;
	private $_initialStatusId;

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
	public function getId()
	{
		return $this->_id;
	}
	public function getInitialStatusId()
	{
		return $this->_initialStatusId;
	}
}
