<?php
namespace raoul2000\workflow\validation;

use Yii;
use yii\validators\Validator;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;

/**
 * WorkflowValidator run validation for the current workflow event.
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

	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('app', 'Error on {attribute}.');
		}
	}
	/**
	 * Apply active validators for the current workflow event sequence.
	 *
	 * If a workflow event sequence is about to occur, this method scan all validators defined in the
	 * owner model, and applies the ones which are valid for the upcomming events.
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
	 *
	 * @param yii\validators\Validator $validator The validator instance to test
	 * @param WorklflowEvent $event The workflow event for which the validator is tested
	 * @return boolean
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
