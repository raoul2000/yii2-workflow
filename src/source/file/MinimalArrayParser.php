<?php

namespace raoul2000\workflow\source\file;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use raoul2000\workflow\base\WorkflowValidationException;
use yii\helpers\VarDumper;
/**
 * Parse a workflow definition provided as a minimal PHP array.
 *
 * Following rules apply :
 *
 * - the array must be associative, each key being a status Id, and each value is an array of target status id.
 * - no 'initialStatusId' is required : the first status defined is considered as the initial status
 * - no additional attribute is supported : label, metadata, transition
 *
 * For example :
 * <pre>
 * [
 *	'draft'     => ['ready', 'delivered'],
 *	'ready'     => ['draft', 'delivered'],
 *	'delivered' => ['payed', 'archived'],
 *	'payed'     => ['archived'],
 *	'archived'  => []
 * ]
 * </pre>
 *
 * You can also use a comma separated list of status for the end status list instead of an array.
 * For example :
 * <pre>
 * [
 *	'draft'     => 'ready, delivered',
 *	'ready'     => 'draft, delivered',
 *	'delivered' => 'payed, archived',
 *	'payed'     => 'archived',
 *	'archived'  => []
 * ]
 * </pre>
 */
class MinimalArrayParser extends WorkflowArrayParser {



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

		if ( empty($wId)) {
			throw new WorkflowValidationException("Missing argument : workflow Id");
		}
		if ( ! \is_array($definition)) {
			throw new WorkflowValidationException("Workflow definition must be provided as an array");
		}

		if ( ! ArrayHelper::isAssociative($definition)) {
			throw new WorkflowValidationException("Workflow definition must be provided as associative array");
		}

		$initialStatusId    = null;
		$normalized 		= [];
		$startStatusIdIndex = [];
		$endStatusIdIndex   = [];

		foreach($definition as $id => $targetStatusList) {
			list($workflowId, $statusId) = $source->parseStatusId($id, $wId);
			$absoluteStatusId = $workflowId . WorkflowFileSource::SEPARATOR_STATUS_NAME .$statusId;
			if ( $workflowId != $wId) {
				throw new WorkflowValidationException('Status must belong to workflow : ' . $absoluteStatusId);
			}
			if (count($normalized) == 0) {
				$initialStatusId = $absoluteStatusId;
				$normalized['initialStatusId'] = $initialStatusId;
				$normalized[WorkflowFileSource::KEY_NODES] = [];
			}
			$startStatusIdIndex[] = $absoluteStatusId;
			$endStatusIds = [];
			if ( \is_string($targetStatusList)) {
				$ids = array_map('trim', explode(',', $targetStatusList));
				$endStatusIds = $this->normalizeStatusIds($ids, $wId, $source);
			}elseif ( \is_array($targetStatusList)) {
				if( ArrayHelper::isAssociative($targetStatusList,false) ){
					throw new WorkflowValidationException("Associative array not supported (status : $absoluteStatusId)");
				}
				$endStatusIds = $this->normalizeStatusIds($targetStatusList, $wId, $source);
			}elseif ( $targetStatusList === null ) {
				$endStatusIds = [];
			}else {
				throw new WorkflowValidationException('End status list must be an array for status  : ' . $absoluteStatusId);
			}

			if ( count($endStatusIds)) {
				$normalized[WorkflowFileSource::KEY_NODES][$absoluteStatusId] = ['transition' => array_fill_keys($endStatusIds,[])];
				$endStatusIdIndex = \array_merge($endStatusIdIndex, $endStatusIds);
			} else {
				$normalized[WorkflowFileSource::KEY_NODES][$absoluteStatusId] = null;
			}
		}

		$this->validate($wId, $source, $initialStatusId, $startStatusIdIndex, $endStatusIdIndex);

		return $normalized;
	}


	/**
	 *
	 * @param array $ids
	 * @param string $workflowId
	 */
	private function normalizeStatusIds($ids, $workflowId, $source)
	{
		$normalizedIds = [];
		foreach ($ids as $id) {
			$pieces = $source->parseStatusId($id, $workflowId);
			$normalizedIds[] = \implode(WorkflowFileSource::SEPARATOR_STATUS_NAME, $pieces);
		}
		return $normalizedIds;
	}
}
