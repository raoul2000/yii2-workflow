<?php
namespace raoul2000\workflow\validation;

use Yii;
use yii\validators\Validator;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;

/**
 * WorkflowValidator run validation for the current workflow event.
 * This validator execute validation for all attributes belonging to the model
 * and being configured for a scenario name that matches the sequence of scenario
 * that is occuring.
 *
 * For example, the the model is going from status A to status B, this validator
 * - creates a scenario sequence (leave status {A}, from {A} to {B}, enter status {B})
 * - select attributes configured to be validated of compatibles scenario
 * - run validation for all the previously selected attributes
 * 
 * @author raoul
 *
 */
class WorkflowValidator extends Validator
{
	/**
	 * Overloads the default initialization value because by default, we want to run the validation
	 * even if the status attribute is null (which is considered as a 'leaveWorkflow' event).
	 *
	 * @var boolean see yii\validators§\Validator
	 */
	public $skipOnEmpty = false;

	/**
	 * Apply active validators for the current workflow event sequence.
	 *
	 * If a workflow event sequence is about to occur, this method scan all validators defined in the
	 * owner model, and applies the ones which are compatibles for the upcomming events.
	 *
	 * @see \raoul2000\workflow\events\IEventSequence
	 */
	public function validateAttribute($object, $attribute)
	{
		if (  ! SimpleWorkflowBehavior::isAttachedTo($object) ) {
			throw new WorkflowException('Validation error : the model does not have the SimpleWorkflowBehavior');
		}

		try {
			$scenarioList= $object->getScenarioSequence($object->$attribute);
		} catch (WorkflowException $e) {
			$object->addError($attribute, 'Workflow validation failed : '.$e->getMessage());
			$scenarioList = [];
		}

		if ( count($scenarioList) != 0 ) {
			foreach ($object->getValidators() as $validator) {
				foreach ($scenarioList as $scenario) {
					if ($this->_isActiveValidator($validator, $scenario)) {
						$validator->validateAttributes($object);
					}
				}
			}
		}
	}

	/**
	 * Checks if a validator is active for the workflow event passed as argument.
	 * A Validator is active if it is configured for a scenario that matches the
	 * current scenario.
	 *
	 * @param yii\validators\Validator $validator The validator instance to test
	 * @param WorklflowEvent $event The workflow event for which the validator is tested
	 * @return boolean TRUE if the validtor is active, FALSE otherwise.
	 */
	private function _isActiveValidator($validator, $currentScenario)
	{
		foreach ($validator->on as $scenario) {
			if ( WorkflowScenario::match($scenario, $currentScenario)) {
				return true;
			}
		}
		return false;
	}
}
