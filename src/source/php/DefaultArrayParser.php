<?php 

namespace raoul2000\workflow\source\php;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use raoul2000\workflow\base\WorkflowValidationException;
use yii\helpers\VarDumper;

class DefaultArrayParser extends Object implements IArrayParser {
	
	/**
	 * @var boolean when TRUE, the parse method will also perform some validations
	 */
	public $validate = true;

	/**
	 * Parse a workflow defined as a PHP Array.
	 *
	 * The workflow definition passed as argument is turned into an array that can be
	 * used by the WorkflowPhpSource components. 
	 * 
	 * @param string $wId
	 * @param array $definition
	 * @param raoul2000\workflow\source\php\WorkflowPhpSource $source
	 * @return array The parse workflow array definition
	 * @throws WorkflowValidationException
	 */
	public function parse($wId, $definition, $source) {
	
		$normalized = [];
		if ( ! isset($definition['initialStatusId'])) {
			throw new WorkflowValidationException('Missing "initialStatusId"');
		}
	
		$pieces = $source->parseStatusId( $definition['initialStatusId'],null,$wId);
		$initialStatusId = \implode(WorkflowPhpSource::SEPARATOR_STATUS_NAME, $pieces);
	
		if ( ! isset($definition[WorkflowPhpSource::KEY_NODES])) {
			throw new WorkflowValidationException("No status definition found");
		}
		$normalized['initialStatusId'] = $initialStatusId;
	
		if (! \is_array($definition[WorkflowPhpSource::KEY_NODES])) {
			throw new WorkflowValidationException('Invalid Status definition : array expected');
		}
		$startStatusIdIndex = [];
		$endStatusIdIndex = [];
	
		foreach($definition[WorkflowPhpSource::KEY_NODES] as $key => $value) {
			$startStatusId = null;
			$startStatusDef = null;
			if ( is_string($key) ) {
				/**
				 * 'status' => ['A' => ???]
				 */
				$startStatusId = $key;
				if( $value == null) {
					$startStatusDef = $startStatusId;	// 'status' => ['A' => null]
				}elseif (  \is_array($value)) {
					$startStatusDef = $value;			// 'status' => ['A' => [ ...] ]
				}else {
					throw new WorkflowValidationException("Wrong definition for status $startStatusId : array expected");
				}
			} elseif ( is_string($value)) {
				/**
				 * 'status' => 'A'
				 */
				$startStatusId = $value;
				$startStatusDef = $startStatusId;
			} else {
				throw new WorkflowValidationException("Wrong status definition : key = " . VarDumper::dumpAsString($key). " value = ". VarDumper::dumpAsString($value));
			}
	
			$pieces = $source->parseStatusId($startStatusId,null,$wId);
			$startStatusId = $startStatusIdIndex[] = \implode(WorkflowPhpSource::SEPARATOR_STATUS_NAME, $pieces);
				
			if ( is_array($startStatusDef) ) {
	
				if( count($startStatusDef) == 0) {
					/**
					 * empty status config array
					 *
					 * 'A' => []
					 */
					$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId] = null;
				} else {
					foreach ($startStatusDef as $startStatusKey => $startStatusValue) {
						if ( $startStatusKey == WorkflowPhpSource::KEY_METADATA )
						{
							/**
							 * validate metadata
							 *
							 * 'A' => [
							 * 		'metadata' => [ 'key' => 'value']
							 * ]
							 */
								
							if ( \is_array($startStatusDef[WorkflowPhpSource::KEY_METADATA])) {
								if (! ArrayHelper::isAssociative($startStatusDef[WorkflowPhpSource::KEY_METADATA])) {
									throw new WorkflowValidationException("Invalid metadata definition for status $startStatusId : associative array expected");
								}
							} else {
								throw new WorkflowValidationException("Invalid metadata definition for status $startStatusId : array expected");
							}
							$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId][WorkflowPhpSource::KEY_METADATA] = $startStatusDef[WorkflowPhpSource::KEY_METADATA];
						}
						elseif ( $startStatusKey == 'transition')
						{
							$transitionDefinition = $startStatusDef['transition'];
							if ( \is_string($transitionDefinition)) {
								/**
								 *  'A' => [
								 *   	'transition' => 'A, B, WID/C'
								 *   ]
								 */
								$ids = array_map('trim', explode(',', $transitionDefinition));
								foreach ($ids as $id) {
									$pieces = $source->parseStatusId($id,null,$wId);
									$canEndStId = \implode(WorkflowPhpSource::SEPARATOR_STATUS_NAME, $pieces);
									$endStatusIdIndex[] = $canEndStId;
									$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId]['transition'][$canEndStId] = [];
								}
							} elseif ( \is_array($transitionDefinition)) {
								/**
								 *  'transition' => [ ...]
								 */
								foreach( $transitionDefinition as $tkey => $tvalue) {
									if ( \is_string($tkey)) {
										/**
										 * 'transition' => [ 'A' => [] ]
										 */
										$endStatusId = $tkey;
										if ( ! \is_array($tvalue)) {
											throw new WorkflowValidationException("Wrong definition for between $startStatusId and $endStatusId : array expected");
										}
										$transDef = $tvalue;
									} elseif ( \is_string($tvalue)){
										/**
										 * 'transition' =>  'A' 
										 */
										$endStatusId = $tvalue;
										$transDef = null;
									} else {
										throw new WorkflowValidationException("Wrong transition definition for status $startStatusId : key = "
												. VarDumper::dumpAsString($tkey). " value = ". VarDumper::dumpAsString($tvalue));
									}
										
									$pieces = $source->parseStatusId($endStatusId,null,$wId);
									$canEndStId = \implode(WorkflowPhpSource::SEPARATOR_STATUS_NAME, $pieces);
									$endStatusIdIndex[] = $canEndStId;
										
									if ( $transDef != null) {
										$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId]['transition'][$canEndStId] = $transDef;
									}else {
										$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId]['transition'][$canEndStId] = [];
									}
								}
							} else {
								throw new WorkflowValidationException("Invalid transition definition format for status $startStatusId : string or array expected");
							}
						}
						elseif ( \is_string($startStatusKey)) {
							$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId][$startStatusKey] = $startStatusValue;
						}
					}
				}
			} else { //$startStatusDef is not array
				/**
				 * Node IDS must be canonical and array keys
				 * 'status' => [
				 * 		'A'
				 * ]
				 *  turned into
				 *
				 * 'status' => [
				 * 		'WID/A' => null
				 * ]
	
				 */
				$normalized[WorkflowPhpSource::KEY_NODES][$startStatusId] = null;
			}
		}
	
		// copy remaining workflow properties
		foreach($definition as $propName => $propValue) {
			if( is_string($propName)) {
				if( $propName != 'initialStatusId' && $propName != WorkflowPhpSource::KEY_NODES) {
					$normalized[$propName] = $propValue;
				}
			}
		}
		
		if ( $this->validate === true) {
			if ( ! \in_array($initialStatusId, $startStatusIdIndex)) {
				throw new WorkflowValidationException("Initial status not defined : $initialStatusId");
			}
		
			// detect not defined statuses
		
			$missingStatusIdSuspects = \array_diff($endStatusIdIndex, $startStatusIdIndex);
			if ( count($missingStatusIdSuspects) != 0) {
				$missingStatusId = [];
				foreach ($missingStatusIdSuspects as $id) {
					list($thisWid, $thisSid) = $source->parseStatusId($id,null,$wId);
					if ($thisWid == $wId) {
						$missingStatusId[] = $id; // refering to the same workflow, this Id is not defined
					}
				}
				if ( count($missingStatusId) != 0) {
					throw new WorkflowValidationException("One or more end status are not defined : ".VarDumper::dumpAsString($missingStatusId));
				}
			}
		}
		return $normalized;
	}	
} 