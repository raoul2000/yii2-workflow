<?php

namespace raoul2000\workflow\source\file;

use Yii;
use yii\helpers\ArrayHelper;
use raoul2000\workflow\base\WorkflowValidationException;
use yii\helpers\VarDumper;

/**
 * Implements a parser for default PHP array formatted workflow.
 *
 * Example :
 * <pre>
 * [
 *   'initialStatusId' => 'draft',
 *   'status' => [
 *       'draft'     => [
 *           'label'      => 'Draft State'
 *           'transition' => 'published'
 *       ],
 *       'published' => [
 *           'transition' => ['draft','published']
 *       ],
 *       'archived'
 *   ]
 * ]
 * </pre>
 *
 */
class DefaultArrayParser extends WorkflowArrayParser {

	/**
	 * @var boolean when TRUE, the parse method will also perform some validations
	 */
	public $validate = true;

	/**
	 * Parse a workflow defined as a PHP Array.
	 *
	 * The workflow definition passed as argument is turned into an array that can be
	 * used by the WorkflowFileSource components.
	 *
	 * @param string $wId
	 * @param array $definition
	 * @param raoul2000\workflow\source\file\WorkflowFileSource $source
	 * @return array The parse workflow array definition
	 * @throws WorkflowValidationException
	 */
	public function parse($wId, $definition, $source) {

		$normalized = [];
		if ( ! isset($definition['initialStatusId'])) {
			throw new WorkflowValidationException('Missing "initialStatusId"');
		}

		list($workflowId, $statusId) = $source->parseStatusId( $definition['initialStatusId'],$wId);
		$initialStatusId = $workflowId . WorkflowFileSource::SEPARATOR_STATUS_NAME .$statusId;
		if( $workflowId != $wId) {
			throw new WorkflowValidationException('Initial status must belong to workflow : '.$initialStatusId);
		}

		if ( ! isset($definition[WorkflowFileSource::KEY_NODES])) {
			throw new WorkflowValidationException("No status definition found");
		}
		$normalized['initialStatusId'] = $initialStatusId;

		if (! \is_array($definition[WorkflowFileSource::KEY_NODES])) {
			throw new WorkflowValidationException('Invalid Status definition : array expected');
		}
		$startStatusIdIndex = [];
		$endStatusIdIndex = [];

		foreach($definition[WorkflowFileSource::KEY_NODES] as $key => $value) {
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

			list($workflowId, $statusId) = $source->parseStatusId($startStatusId,$wId);
			$startStatusId = $startStatusIdIndex[] = $workflowId . WorkflowFileSource::SEPARATOR_STATUS_NAME . $statusId;
			if( $workflowId != $wId) {
				throw new WorkflowValidationException('Status must belong to workflow : '.$startStatusId);
			}

			if ( is_array($startStatusDef) ) {

				if( count($startStatusDef) == 0) {
					/**
					 * empty status config array
					 *
					 * 'A' => []
					 */
					$normalized[WorkflowFileSource::KEY_NODES][$startStatusId] = null;
				} else {
					foreach ($startStatusDef as $startStatusKey => $startStatusValue) {
						if ( $startStatusKey == WorkflowFileSource::KEY_METADATA )
						{
							/**
							 * validate metadata
							 *
							 * 'A' => [
							 * 		'metadata' => [ 'key' => 'value']
							 * ]
							 */

							if ( \is_array($startStatusDef[WorkflowFileSource::KEY_METADATA])) {
								if (! ArrayHelper::isAssociative($startStatusDef[WorkflowFileSource::KEY_METADATA])) {
									throw new WorkflowValidationException("Invalid metadata definition for status $startStatusId : associative array expected");
								}
							} else {
								throw new WorkflowValidationException("Invalid metadata definition for status $startStatusId : array expected");
							}
							$normalized[WorkflowFileSource::KEY_NODES][$startStatusId][WorkflowFileSource::KEY_METADATA] = $startStatusDef[WorkflowFileSource::KEY_METADATA];
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
									$pieces = $source->parseStatusId($id,$wId);
									$canEndStId = \implode(WorkflowFileSource::SEPARATOR_STATUS_NAME, $pieces);
									$endStatusIdIndex[] = $canEndStId;
									$normalized[WorkflowFileSource::KEY_NODES][$startStatusId]['transition'][$canEndStId] = [];
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

									$pieces = $source->parseStatusId($endStatusId,$wId);
									$canEndStId = \implode(WorkflowFileSource::SEPARATOR_STATUS_NAME, $pieces);
									$endStatusIdIndex[] = $canEndStId;

									if ( $transDef != null) {
										$normalized[WorkflowFileSource::KEY_NODES][$startStatusId]['transition'][$canEndStId] = $transDef;
									}else {
										$normalized[WorkflowFileSource::KEY_NODES][$startStatusId]['transition'][$canEndStId] = [];
									}
								}
							} else {
								throw new WorkflowValidationException("Invalid transition definition format for status $startStatusId : string or array expected");
							}
						}
						elseif ( \is_string($startStatusKey)) {
							$normalized[WorkflowFileSource::KEY_NODES][$startStatusId][$startStatusKey] = $startStatusValue;
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
				$normalized[WorkflowFileSource::KEY_NODES][$startStatusId] = null;
			}
		}

		// copy remaining workflow properties
		foreach($definition as $propName => $propValue) {
			if( is_string($propName)) {
				if( $propName != 'initialStatusId' && $propName != WorkflowFileSource::KEY_NODES) {
					$normalized[$propName] = $propValue;
				}
			}
		}

		$this->validate($wId, $source, $initialStatusId, $startStatusIdIndex, $endStatusIdIndex);

		return $normalized;
	}
}
