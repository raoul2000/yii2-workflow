<?php
namespace raoul2000\workflow\source\file;

use Yii;
use yii\base\Object;
use raoul2000\workflow\base\WorkflowException;

class GraphmlLoader extends WorkflowDefinitionLoader
{

	/**
	 * @var string path where the graphml file to load is located
	 */
	public $path = '@app/models';
	/**
	 * Maps a named attribute with a id attribute defined in the input graphml file.
	 * 
	 * @var array
	 */
	private $_mapper = [
		// workflow
		'w-intial-node-id' => "/ns:graphml/ns:key[@for='graph'][@attr.name='initialStatusId']/@id",
		
		// nodes
		'n-label' => "/ns:graphml/ns:key[@for='node'][@attr.name='label']/@id",
		'n-graphics' => "/ns:graphml/ns:key[@for='node'][@yfiles.type='nodegraphics']/@id"
	];

	/**
	 *
	 * @var DOMDocument XML DOM parser
	 */
	private $_dom;

	/**
	 *
	 * @var DOMXPath The XPath object used to evaluate all xpath expressions
	 */
	private $_xp;

	/**
	 *
	 * @var array map between named properties and graphml properties extracted from the input
	 *      file. This array is builte based on the <em>_mapper</em> array.
	 */
	private $_yedProperties = [];
	
	/**
	 * Loads the definition of the workflow whose id is passed as argument.
	 * 
	 * @param string $workflowId
	 * @param IWorkflowSource the workflow source component
	 * @throws WorkflowException
	 * @return array the workflow definition
	 */
	public function loadDefinition($workflowId,$source)
	{
		 $wd = $this->convert(
			$this->createFilename($workflowId)
		);
		 return $this->parse($workflowId, $wd, $source);
	}
	
	public function createFilename($workflowId)
	{
		return Yii::getAlias($this->path) . '/' . $workflowId . '.graphml';
	}	
	/**
	 * Convert a graphml file describing a workflow into an array suitable to create a <em>workflow</em>
	 * object.
	 * 
	 * @param string $graphmlFile
	 *        	the path to the graphml file to process
	 */
	public function convert($graphmlFile)
	{
		$this->_dom = new \DOMDocument();
		$this->_dom->load($graphmlFile);
		
		$this->_xp = new \DOMXPath($this->_dom);
		$this->_xp->registerNamespace('ns', 'http://graphml.graphdrawing.org/xmlns');
		$this->_xp->registerNamespace('y', 'http://www.yworks.com/xml/graphml');
		
		$this->extractYedProperties();
		if(!isset($this->_yedProperties['w-intial-node-id'])) {
			throw new WorkflowException("Missing custom workflow property : 'initialStatusId'");
		}
		$workflow = $this->collectWorkflowProperties();
		$nodes = $this->collectNodes();
		$edges = $this->collectTransitions();
		
		//return ['nodes'=> $nodes, 'edges' => $edges];
		return $this->createWorkflowDefinition($workflow, $nodes, $edges);
	}

	/**
	 * Merges all arrays extracted from the graphml file (workflow, nodes, edges) to create and
	 * return a single array descrbing a workflow.
	 *
	 * @param [] $w
	 *        	workflow attributes
	 * @param [] $n
	 *        	nodes attributes
	 * @param [] $e
	 *        	edges attributes
	 */
	private function createWorkflowDefinition($w, $n, $e)
	{
		$nodes = [];
		
		foreach ($n as $key => $node) {
			$newNode = [];
			if (isset($node['label'])) {
				$newNode['label'] = $node['label'];
			}
			$newNode['metadata'] = [
				'background-color' => $node['background-color'],
				'color' => $node['color']
			];
			$newNode['transition'] = [];
			if (isset($e[$key])) {
				foreach ($e[$key] as $trgKey => $edge) {
					$newNode['transition'][] = $n[$trgKey]['id']; // $edge;
				}
			}
			$nodes[$node['id']] = $newNode;
		}
		return [
			'initialStatusId' => $w['initial'],
			'status' => $nodes
		];
	}

	/**
	 * Retrieve the graphml id attribute for each named properties defines in the <em>_mapper</em>
	 * array.
	 */
	private function extractYedProperties()
	{
		foreach ($this->_mapper as $attrName => $xp) {
			$nodeList = $this->_xp->query($xp);
			if ($nodeList->length != 0) {
				$this->_yedProperties[$attrName] = $nodeList->item(0)->value;
			}
		}
	}
	/**
	 *
	 * @throws WorkflowException
	 */
	private function collectWorkflowProperties()
	{
		$nlGraph = $this->_xp->query('//ns:graph');
		if ($nlGraph->length == 0) {
			throw new WorkflowException("no workflow definition found");
		}
		
		if ($nlGraph->length > 1) {
			throw new WorkflowException("more than one workflow found");
		}
		
		// extract custom properties /////////////////////////////////////////////////////////////////
		// INITIAL
		
		$nl2 = $this->_xp->query('/ns:graphml/ns:graph/ns:data[@key="' . $this->_yedProperties['w-intial-node-id'] . '"]');
		if ($nl2->length != 1 || $this->isBlank($nl2->item(0)->nodeValue)) {
			throw new WorkflowException("failed to extract initial node id for this workflow");
		}
		
		$result = [
			'initial' => trim($nl2->item(0)->nodeValue)
		];
		
		return $result;
	}

	/**
	 * Extract edges defined in the graphml input file
	 * 
	 * @return []
	 * @throws WorkflowException
	 */
	private function collectTransitions()
	{
		$nlEdges = $this->_xp->query('//ns:edge');
		if ($nlEdges->length == 0) {
			throw new WorkflowException("no edge could be found in this workflow");
		}
		
		$result = [];
		for ($i = 0; $i < $nlEdges->length; $i ++) {
			$currentNode = $nlEdges->item($i);
			
			$source = trim($this->_xp->query("@source", $currentNode)->item(0)->value);
			$target = trim($this->_xp->query("@target", $currentNode)->item(0)->value);
			
			if (! isset($result[$source]) || ! isset($result[$source][$target])) {
				$result[$source][$target] = [];
			}
		}
		return $result;
	}

	/**
	 * Extract nodes defined in the graphml input file.<br/>
	 * When working with yEd, remember that the node 'label' is used as the node id by workflow.
	 * This is the only
	 * required value for a valid node. A node with no label in yEd will be ingored by this converter.
	 *
	 * @return []
	 * @throws WorkflowException
	 */
	private function collectNodes()
	{
		$nlNodes = $this->_xp->query('//ns:node');
		if ($nlNodes->length == 0) {
			throw new WorkflowException("no node could be found in this workflow");
		}
		
		$result = [];
		for ($i = 0; $i < $nlNodes->length; $i ++) {
			$currentNode = $nlNodes->item($i);
			$nl2 = $this->_xp->query("@id", $currentNode);
			
			if ($nl2->length != 1) {
				throw new WorkflowException("failed to extract yed node id");
			}
			
			// yEd node Id
			$yNodeId = trim($nl2->item(0)->value);
			
			// extract mandatory properties ////////////////////////////////////////////////////////////
			
			$nl2 = $this->_xp->query('ns:data[@key="' . $this->_yedProperties['n-graphics'] . '"]/*/y:NodeLabel', $currentNode);
			if ($nl2->length != 1) {
				continue;
			}
			
			$result[$yNodeId] = [];
			$result[$yNodeId]['id'] = trim($nl2->item(0)->nodeValue);
			
			// extract custom properties /////////////////////////////////////////////////////////////////
			
			if (isset($this->_yedProperties['n-label'])) {
				$nl2 = $this->_xp->query('ns:data[@key="' . $this->_yedProperties['n-label'] . '"]', $currentNode);
				if ($nl2->length == 1) {
					$result[$yNodeId]['label'] = trim($nl2->item(0)->nodeValue);
				}
			}
			
			$nl2 = $this->_xp->query('ns:data[@key="' . $this->_yedProperties['n-graphics'] . '"]/*/y:Fill/@color', $currentNode);
			if ($nl2->length == 1) {
				$result[$yNodeId]['background-color'] = trim($nl2->item(0)->nodeValue);
			}
			
			$nl2 = $this->_xp->query('ns:data[@key="' . $this->_yedProperties['n-graphics'] . '"]/*/y:NodeLabel/@textColor', $currentNode);
			if ($nl2->length == 1) {
				$result[$yNodeId]['color'] = trim($nl2->item(0)->nodeValue);
			}
		}
		return $result;
	}

	/**
	 *
	 * @param string $str        	
	 * @return boolean TRUE if the string passed as argument is null, empty, or made of space character(s)
	 */
	private function isBlank($str)
	{
		return ! isset($str) || strlen(trim($str)) == 0;
	}
}