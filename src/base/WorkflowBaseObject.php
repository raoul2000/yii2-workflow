<?php
namespace raoul2000\workflow\base;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use raoul2000\workflow\source\IWorkflowSource;


/**
 * This class is the base class for Workflow, Transition and Status objects.
 *
 * It mainly provides a way to store additional properties without the need to
 * declare them in the class definition. Theses properties are called **metadata** and stored into
 * an array. They can be accessed like regular class properties.
 */
abstract class WorkflowBaseObject extends BaseObject
{
	/**
	 * @var array optional Meatadata are user defined properties where array key is the property name 
	 * and array value the property value
	 */
	private $_metadata = [];
	/**
	 * @var IWorkflowSource workflow source component used to create this Status instance
	 */
	private $_source;
	/**
	 * Construct a workflow object.
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		if ( array_key_exists('metadata', $config) && is_array($config['metadata'])) {
			$this->_metadata = $config['metadata'];
			unset($config['metadata']);
		}
		
		if( array_key_exists('source', $config) ) {
			$this->_source = $config['source'];
			if( ! $this->_source instanceof IWorkflowSource){
				throw new InvalidConfigException('The "source" property must implement interface raoul2000\workflow\source\IWorkflowSource');
			}
			unset($config['source']);
		}
		parent::__construct($config);
	}

	/**
	 *
	 */
	public function __get($name)
	{
		if ( $this->canGetProperty($name)) {
			return parent::__get($name);
		} elseif ( $this->hasMetadata($name)) {
			return  $this->_metadata[$name];
		} else {
			throw new WorkflowException("No metadata found is the name '$name'");
		}
	}
	/**
	 * @return string the object identifier
	 */
	abstract public function getId();
	/**
	 * Returns the value of the metadata with namer `$paramName`.
	 * If no `$paramName`is provided, this method returns an array containing all metadata parameters.
	 * 
	 * @param string $paramName when null the method returns the complet metadata array, otherwise it returns the
	 * value of the correponding metadata.
	 * @param mixed $defaultValue
	 * @throws \yii\base\InvalidConfigException
	 * @return mixed
	 */
	public function getMetadata($paramName = null, $defaultValue = null)
	{
		if ( $paramName === null) {
			return $this->_metadata;
		} elseif( $this->hasMetadata($paramName) ) {
			return $this->_metadata[$paramName];
		} else {
			return $defaultValue;
		}
	}
	/**
	 * Test if a metadata parameter is defined.
	 * 
	 * @param string $paramName the metadata parameter name
	 * @throws \raoul2000\workflow\base\WorkflowException
	 * @return boolean TRUE if the metadata parameter exists, FALSE otherwise
	 */
	public function hasMetadata($paramName)
	{
		if ( ! is_string($paramName) || empty($paramName)) {
			throw new WorkflowException("Invalid metadata name : non empty string expected");
		}
		return array_key_exists($paramName, $this->_metadata);
	}
	/**
	 * Returns the source workflow component used to create this instance.
	 * 
	 * @return \raoul2000\workflow\source\IWorkflowSource the source instance or null if no 
	 * source was been provided
	 */
	public function getSource()
	{
		return $this->_source;
	}
}
