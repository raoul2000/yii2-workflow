<?php
namespace raoul2000\workflow\source\file;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
/**
 * The WorkflowDefinitionLoader is the base class for all implementations of workflow definition
 * loaders.
 */
abstract class WorkflowDefinitionLoader extends BaseObject
{
	/**
	 * The parser component.
	 *
	 * @var array|string|object| false
	 */
	public $parser = null;
	/**
	 *
	 * @var [type] The parser component instance
	 */
	private $_p;

	/**
	 * Loads the definition of a workflow.
	 *
	 * @param string $workflowId
	 * @param IWorkflowSource $source
	 */
	abstract public function loadDefinition($workflowId, $source);

	/**
	 * Initialize the parser component to use.
	 */
	public function init() {
		parent::init();
		if( $this->parser === null ) {
			$this->_p = Yii::createObject([
				'class' => DefaultArrayParser::className()
			]);
		} elseif( $this->parser === false) {
			$this->_p = null;
		} elseif( is_array($this->parser)) {
			$this->_p = Yii::createObject($this->parser);
		} elseif( is_string($this->parser)) {
			$this->_p = Yii::$app->get($this->parser);
		} elseif( is_object($this->parser)) {
			$this->_p = $this->parser;
		} else {
			throw new InvalidConfigException('invalid "parser" attribute : string or array expected');
		}

		if( $this->_p !== null && ! $this->_p instanceof WorkflowArrayParser ) {
			throw new InvalidConfigException('the parser component must implement the WorkflowArrayParser interface');
		}
	}
	/**
	 * Returns the instance of the array parser used.
	 *
	 * @returnWorkflowArrayParser the parser component used by this instance or NULL if no parser has been configured
	 */
	public function getParser()
	{
		return $this->_p;
	}
	/**
	 *
	 * @param string $workflowId
	 * @param array $wd
	 * @param WorkflowFileSource $source
	 * @return array The workflow definition
	 */
	public function parse($workflowId, $wd, $source)
	{
		if( $this->_p !== null ) {
			return $this->_p->parse($workflowId, $wd, $source);
		} else {
			return $wd;
		}
	}
}
