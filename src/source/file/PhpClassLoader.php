<?php

namespace raoul2000\workflow\source\file;

use Yii;
use yii\base\Object;
use raoul2000\workflow\base\WorkflowException;
use yii\base\InvalidConfigException;

class PhpClassLoader extends WorkflowDefinitionLoader {
	/**
	 * @var string namespace where workflow definition class are located
	 */
	public $namespace = 'app\models';

	/**
	 * 
	 * @param unknown $workflowId
	 * @param unknown $source
	 * @throws WorkflowException
	 */
	public function loadDefinition($workflowId, $source)
	{
		$wfClassname = $this->getClassname($workflowId);
		$defProvider = null;
		try {
			$defProvider = Yii::createObject(['class' => $wfClassname]);
		} catch ( \ReflectionException $e) {
			throw new WorkflowException('failed to load workflow definition : '.$e->getMessage());
		}	
		if( ! method_exists($defProvider, 'getDefinition')) {
			throw new WorkflowException('Invalid workflow provider class : '.$wfClassname);
		}
		
		return $this->parse($workflowId, $defProvider->getDefinition(), $source);
	}
	
	/**
	 * Returns the complete name for the Workflow Provider class used to retrieve the definition of workflow $workflowId.
	 * The class name is built by appending the workflow id to the namespace parameter set for this source component.
	 *
	 * @param string $workflowId a workflow id
	 * @return string the full qualified class name used to provide definition for the workflow
	 */
	public function getClassname($workflowId)
	{
		return $this->namespace . '\\' . $workflowId;
	}	
}