<?php

namespace raoul2000\workflow\source\file;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * Loads a workflow definition from a PHP file.
 * The PHP filename is built by concatenating the $path and the $workflowId values,
 * adding the '.php' extension.
 * 
 * This file must return the workflow definition in the form of an associative array
 * having the appropriate structure depending on the configured parser.
 *
 */
class PhpArrayLoader extends WorkflowDefinitionLoader {
	/**
	 * @var string path where the php file to load is located
	 */
	public $path = '@app/models/workflows';
	
	/**
	 * Loads and returns the workflow definition available as a PHP array in a file.
	 * If a parser has been configured, it is used to converte the array structure defined
	 * in the file, into the format expected by the WorkflowFileSource class.
	 * 
	 * @param string $workflowId
	 * @param WorkflowFileSource $source
	 * @return array the workflow definition
	 * @throws WorkflowException
	 */
	public function loadDefinition($workflowId,$source)
	{
		$wd = require_once($this->createFilename($workflowId));
		return $this->parse($workflowId, $wd, $source);
	}
	/**
	 * Creates and returns the absolute filename of the PHP file that contains
	 * the workflow definition to load.
	 * 
	 * @param string $workflowId
	 * @return string the absolute file path of the workflow definition file
	 */
	public function createFilename($workflowId)
	{
		return Yii::getAlias($this->path) . '/' . $workflowId . '.php';
	}
}