<?php

namespace raoul2000\workflow\base;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\events\IEventSequence;
use raoul2000\workflow\validation\WorkflowScenario;
use tests\unit\workflow\behavior\InitStatusTest;
use raoul2000\workflow\events\WorkflowEvent;

/**
 * SimpleWorkflowBehavior implements the behavior of a model evolving inside a *Simple Workflow*.
 *
 * To use *SimpleWorkflowBehavior* with the default parameters, simply attach it to the model class like you would do
 * for any standard Yii2 behavior.
 * 
 * <pre>
 * use raoul2000\workflow\base\SimpleWorkflowBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         'simpleWorkflow' => [
 *             'class' => SimpleWorkflowBehavior::className()
 *         ],
 *     ];
 * }
 * </pre>
 * 
 * To learn more about Yii2 behaviors refer to the [Yii2 Definitive Guide](http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html)
 * 
 * You can customize the *SimpleWorkflowBehavior* with the following parameters :
 *
 * - `statusAttribute` : name of the attribute that is used by the owner model to hold the status value. The
 * default value is "status".
 * - `defaultWorkflowId` : identifier of the default workflow for the owner model. If no value is provided, the behavior
 * creates a default workflow identifier (see  [[getDefaultWorkflowId]]).
 * - `source` : name of the *Workflow Source Component* that the behavior uses to read the workflow definition. By default
 * the component id "workflowSource" is used. If it is not already available in the current application it is created by the behavior using the
 * default workflow source component class.
 *
 * Below is an example behavior initialization :
 * 
 * <pre>
 * use raoul2000\workflow\base\SimpleWorkflowBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         'simpleWorkflow' => [
 *             'class' => SimpleWorkflowBehavior::className(),
 *             'statusAttribute' => 'col_status',
 *             'defaultWorkflowId' => 'MyWorkflow',
 *             'source' => 'myWorkflowSource',
 *         ],
 *     ];
 * }
 * </pre>
 * 
 * Please note that the model must be an instance of yii\db\BaseActiveRecord.
 */
class SimpleWorkflowBehavior extends Behavior
{
	/**
	 * Name of the class used to instantiate the default workflow source component if not 
	 * configured.
	 */
	const DEFAULT_SOURCE_CLASS = 'raoul2000\workflow\source\file\WorkflowFileSource';
	/**
	 * Name of the class used to instantiate the default event sequence if not 
	 * configured.
	 */
	const DEFAULT_EVENT_SEQUENCE_CLASS = 'raoul2000\workflow\events\BasicEventSequence';
	/**
	 * Name of the default workflow event fired before the owner model change status.
	 */
	const EVENT_BEFORE_CHANGE_STATUS = 'EVENT_BEFORE_CHANGE_STATUS';
	/**
	 * Name of the default workflow event fired after the owner model change status.
	 */
	const EVENT_AFTER_CHANGE_STATUS  = 'EVENT_AFTER_CHANGE_STATUS';
	/**
	 * @var string name of the owner model attribute used to store the current status value. It is also possible
	 * to use a model property but in this case you must provide a suitable status accessor component that will handle
	 * status persistence.
	 */
	public $statusAttribute = 'status';
	/**
	 * @var string name of the workflow source component to use with the behavior
	 */
	public $source = 'workflowSource';
	/**
	 * @var NULL|string|array|object The status converter component definition or NULL (default) if no
	 * status converter is used by this behavior.<br/>
	 * When not null, the value of this attribute can be specified in one of the following forms :
	 *
	 * - string : name of an existing status converter component registered in the current Yii::$app.
	 * - object : the instance of the status converter
	 *
	 * Note that the status converter configured here must implement the
	 * `raoul2000\workflow\base\IStatusIdConverter` interface.
	 */	
	public $statusConverter = null;
	/**
	 * @var NULL|string|array|object The status accessor component definition or NULL (default) if no
	 * status accessor is used by this behavior.<br/>
	 * When not null, the value of this attribute can be specified in one of the following forms :
	 *
	 * - string : name of an existing status accessor component registered in the current Yii::$app.
	 * - object : the instance of the status converter
	 *
	 * Note that the status accessor configured here must implement the
	 * `raoul2000\workflow\base\IStatusAccessor` interface.
	 */	
	public $statusAccessor = null;
	/**
	 * @var string name of the event sequence provider component. If the component does not exist it is created
	 * by this behavior using the default event sequence class.
	 * Set this attribute to NULL if you are not going to use any Workflow Event.
	 */
	public $eventSequence = 'eventSequence';
	/**
	 * @var bool|string if TRUE, the model is automatically inserted into the default workflow. If 
	 * $autoInsert contains a string, it is assumed to be an initial status Id that will be used to set the 
	 * status. If FALSE (default) the status is not modified. 
	 * NOT_IMPLEMENTED
	 */
	public $autoInsert = false;
	/**
	 * @var bool If TRUE, all errors that may be registred on an invalidated 'before' event, are assigned to
	 * the status attribute of the owner model.
	 */
	public $propagateErrorsToModel = false;	
	/**
	 * @var bool if TRUE, all "before" events are fired even if one of them is invalidated by an attached handler.
	 * When FALSE, the first invalidated event interrupts the event sequence. Note that if an event is attached to 
	 * several handlers, they will all be invoked unless the event is invalidated and marked as handled.
	 * @see WorkflowEvent::invalidate()
	 */
	public $stopOnFirstInvalidEvent = true;
	/**
	 * @var bool When TRUE, a default event is fired on each status change, including when the model enters or leaves the
	 * workflow and even if no event sequence is configured. When FALSE the default event is not fired.
	 */
	public $fireDefaultEvent = true;
	/**
	 * @var string Read only property that contains the id of the default workflow to use with
	 * this behavior.
	 */
	private $_defaultWorkflowId;
	/**
	 * @property Status|null Internal value of the owner model status. This is the real value of the owner model status. It is
	 * maintained internally, depending on the path the owner model is going through within a workflow.
	 * Use getworkflowStatus() to get the actual Status instance.
	 */
	private $_status = null;
	/**
	 * @var IStatusIdConverter Instance of the status ID converter used by this behavior or NULL if no status conversion is done
	 */
	private $_statusConverter = null;
	/**
	 * @var WorkflowSource reference to the workflow source component used by this behavior
	 */
	private $_wfSource;
	/**
	 * @var array workflow events that are fired after save
	 */
	private $_pendingEvents = [];
	/**
	 * @var IEventSequence|null the Event sequence component that provides events to this behavior. If NULL, no event will be
	 * fired by this behavior.
	 */
	private $_eventSequence = null;
	/**
	 * @var IStatusAccessor|null the status accessor component used by this behavior or NULL if no such component is used.
	 */
	private $_statusAccessor = null;
	/**
	 * Instance constructor.
	 * 
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		if ( array_key_exists('defaultWorkflowId', $config)) {
			if (  is_string($config['defaultWorkflowId'])) {
				$this->_defaultWorkflowId = $config['defaultWorkflowId'];
			} else {
				throw new InvalidConfigException("Invalid property Type : 'defaultWorkflowId' must be a string" );
			}
			unset($config['defaultWorkflowId']);
		}
		parent::__construct($config);
	}
	/**
	 * Initialize the behavior.
	 * 
	 * At initialization time, following actions are performed :
	 * 
	 * - get a reference to the workflow source component. If it doesn't exist, it is created.
	 * - get a reference to the event model component or create it if not configured.
	 *  
	 */
	public function init()
	{
		parent::init();

		if (empty($this->statusAttribute)) {
			throw new InvalidConfigException('The "statusAttribute" configuration for the Behavior is required.');
		}

		// init source
		
		if (empty($this->source)) {
			throw new InvalidConfigException('The "source" configuration for the Behavior can\'t be empty.');
		} elseif (  ! Yii::$app->has($this->source)) {
			Yii::$app->set($this->source, ['class'=> self::DEFAULT_SOURCE_CLASS]);
		}
		$this->_wfSource = Yii::$app->get($this->source);

		// init Event Sequence
		
		if ( $this->eventSequence == null) {
			$this->_eventSequence = null;
		} elseif ( is_string($this->eventSequence)) {
			if (  ! Yii::$app->has($this->eventSequence)) {
				Yii::$app->set($this->eventSequence, ['class'=> self::DEFAULT_EVENT_SEQUENCE_CLASS]);
			}
			$this->_eventSequence = Yii::$app->get($this->eventSequence);
		} else {
			throw new InvalidConfigException('Invalid "eventSequence" value.');
		}
	}
	/**
	 * Attaches the behavior to the model.
	 * 
	 * The operation is successful if the owner component has the appropriate attribute or property to store the workflow
	 * status value. The name of this attribute or property is set to 'status' by default, but can be configured  
	 * using the `statusAttribute` configuration parameter at construction time.<br/>
	 * 
	 * Note that using a property instead of a model attribute to store the status value is not recomended as it is then the developer
	 * responsability to ensure that the workflow operations are consistent, in particular regarding persistence.
	 *  
	 * If previous requirements are met, the internal status value is initialized.
	 *
	 * @see \yii\base\Behavior::attach()
	 * @see InitStatus()
	 */
	public function attach($owner)
	{
		parent::attach($owner);
		
		if( $this->owner instanceof \yii\db\BaseActiveRecord ) {
			if( ! $this->owner->hasAttribute($this->statusAttribute) &&  ! $this->owner->hasProperty($this->statusAttribute) ) {
				throw new InvalidConfigException('Attribute or property not found for owner model : \''.$this->statusAttribute.'\'');
			}
		}elseif($this->owner instanceof \yii\base\Object) {
			if(   ! $this->owner->hasProperty($this->statusAttribute) ) {
				throw new InvalidConfigException('Property not found for owner model : \''.$this->statusAttribute.'\'');
			}			
		}
		
		$this->initStatus();
		if( ! $this->hasWorkflowStatus()) {
			$this->doAutoInsert();
		}
	}

	/**
	 * Install events handlers.
	 * 
	 * Following event are used : 
	 * 
	 * - ActiveRecord::EVENT_AFTER_FIND
	 * - ActiveRecord::EVENT_BEFORE_INSERT
	 * - ActiveRecord::EVENT_BEFORE_UPDATE
	 * - ActiveRecord::EVENT_AFTER_UPDATE
	 * - ActiveRecord::EVENT_AFTER_INSERT
	 * - ActiveRecord::EVENT_AFTER_DELETE
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_FIND 		=> 'initStatus',
			ActiveRecord::EVENT_BEFORE_INSERT 	=> 'beforeSaveStatus',
			ActiveRecord::EVENT_BEFORE_UPDATE 	=> 'beforeSaveStatus',
			ActiveRecord::EVENT_AFTER_UPDATE 	=> 'afterSaveStatus',
			ActiveRecord::EVENT_AFTER_INSERT 	=> 'afterSaveStatus',
			ActiveRecord::EVENT_BEFORE_DELETE 	=> 'beforeDelete',
			ActiveRecord::EVENT_AFTER_DELETE 	=> 'afterDelete',
		];
	}
	/**
	 * NOT IMPLEMENTED
	 * 
	 * @throws WorkflowException
	 */
	private function doAutoInsert()
	{
		if ( $this->autoInsert !== false) {
			$workflowId = $this->autoInsert === true ? $this->getDefaultWorkflowId() : $this->autoInsert;
			$workflow = $this->_wfSource->getWorkflow($workflowId);
			if ($workflow !== null) {
				$this->setStatusInternal(
					$this->_wfSource->getStatus($workflow->getInitialStatusId())
				);
			} else {
				throw new WorkflowException("autoInsert failed - No workflow found for id : ".$workflowId);
			}
		}		
	}
	/**
	 * Initialize the internal status value based on the owner model status attribute.
	 *
	 * The <b>status</b> attribute belonging to the owner model is retrieved and if not
	 * empty, converted into the corresponding Status object instance.
	 * This method does not trigger any event, it is only restoring the model into its workflow. It is invoked when the behavior
	 * is attached to the model, and on AFTER_FIND event.
	 * 
	 * @throws raoul2000\workflow\WorkflowException if the status attribute could not be converted into a Status object
	 * @see \raoul2000\workflow\base\StatusInterface.
	 */
	public function initStatus()
	{
		if ( $this->getStatusAccessor() != null) {
			$oStatus = $this->_statusAccessor->readStatus($this->owner);
		} else {
			$oStatus = $this->getOwnerStatus();
		}

		if ( ! empty($oStatus) ) {
			$status = $this->_wfSource->getStatus($oStatus, self::isAttachedTo($this->owner) ? $this->selectDefaultWorkflowId() : null);
			if ($status === null) {
				throw new WorkflowException('Status not found : '.$oStatus);
			}
			$this->setStatusInternal($status);
		} else {
			$this->_status = null;
		}
	}
	
	/**
	 * Puts the owner model into the workflow `$workflowId` or into its default workflow if no
	 * argument is provided.
	 * If the owner model is already in a workflow, an exception is thrown. If this method ends
	 * with no error, the owner model's status is the initial status of the selected workflow.
	 *
	 * @param string $workflowId the ID of the workflow the owner model must be inserted to.
	 * @return boolean TRUE if the operation succeeded, FALSE otherwise
	 * @throws WorkflowException
	 */
	public function enterWorkflow($workflowId = null)
	{
		$result = false;
		if ( $this->hasWorkflowStatus() ) {
			throw new WorkflowException("Model already in a workflow");
		}
		$wId = ( $workflowId === null ? $this->getDefaultWorkflowId() : $workflowId);
		$workflow = $this->_wfSource->getWorkflow($wId);
		if ($workflow !== null) {
			$initialStatusId = $workflow->getInitialStatusId();
			$result = $this->sendToStatusInternal($initialStatusId, false);
		} else {
			throw new WorkflowException("No workflow found for id : ".$wId);
		}
		return $result;
	}

	/**
	 * After the owner model has been saved, fire pending events.
	 *
	 * @param bool $insert
	 * @return boolean
	 */
	public function afterSaveStatus($insert)
	{
		$this->firePendingEvents();
	}

	/**
	 * Send owner model into status if needed.
	 *
	 * @see sendToStatusInternal()
	 * @param yii\base\Event $event
	 */
	public function beforeSaveStatus($event)
	{
		$event->isValid = $this->sendToStatusInternal($this->getOwnerStatus(), true);
	}

	/**
	 * Handle the case where the owner model is leaving the workflow
	 * @param yii\base\Event $event
	 */
	public function beforeDelete($event)
	{
		$event->isValid = $this->sendToStatusInternal(null, true);
	}
	
	/**
	 * Fires pending events, once the owner model has been successfully deleted.
	 * 
	 * @param yii\base\Event $event
	 */
	public function afterDelete($event)
	{
		$this->firePendingEvents();
	}	
	/**
	 * Send the owner model into the status passed as argument.
	 *
	 * If the transition between the current status and `$status` can be performed,
	 * the status attribute in the owner model is updated with the value of the new status, otherwise
	 * it is not changed.
	 * This method can be invoked directly but you should keep in mind that it does not handle status
	 * persistance.
	 *
	 * @param Status|string $status the destination status to reach. If NULL, then the owner model
	 * is going to leave its current workflow.
	 * @return bool TRUE if the transition could be performed, FALSE otherwise
	 */
	public function sendToStatus($status)
	{
		return $this->sendToStatusInternal($status, false);
	}

	/**
	 * Performs status change and event fire.
	 *
	 * This method is called when the owner model is about to change status. This occurs when it is saved, deleted, or when 
	 * a call is done to `sendToStatus()`
	 * 
	 * Based on the current value of the owner model status attribute and the internal behavior status, it checks if a transition
	 * is about to occur. If that's the case, this method fires all "before" events provided by the event sequence component
	 * and then updates status attributes values (both internal and at the owner model level).
	 * Finallly it fires the "after" events, or if we are in a save or delete operation, store them as pending events that are fired
	 * on the *afterSave" or afterDelete event.
	 * Note that if an event handler attached to a "before" event sets the event instance as invalid, all remaining handlers
	 * are ingored and the method returns immediately.
	 *
	 * @param mixed $status the target status or NULL when leaving the workflow
	 * @param boolean $delayed if TRUE, all 'after' events are not fire but stored for being fired in AfterSave or afterDelete. This occurs
	 * when the transition is performed on a save or delete action.
	 * @return boolean
	 */
	private function sendToStatusInternal($status, $delayed)
	{
		$this->_pendingEvents = [];

		list($newStatus, , $events) = $this->createTransitionItems($status, false, true);
		
		$delayedStop = false;
		if ( ! empty($events['before']) ) {
			foreach ($events['before'] as $eventBefore) {
				$this->owner->trigger($eventBefore->name, $eventBefore);
				if ( $eventBefore->isValid === false) {
					if ( $this->propagateErrorsToModel === true && count($eventBefore->getErrors()) != 0 ) {
						$this->owner->addErrors([ $this->statusAttribute => $eventBefore->getErrors() ]);
					}
					if($this->stopOnFirstInvalidEvent === true) {
						return false;
					} else {
						$delayedStop = true;
					}
				}
			}
		}
		if( $delayedStop == true) {
			return false;
		}

		$this->setStatusInternal($newStatus);

		if ( ! empty($events['after']) ) {
			if ( $delayed ) {
				$this->_pendingEvents = $events['after'];
			} else {
				foreach ($events['after'] as $eventAfter) {
					$this->owner->trigger($eventAfter->name, $eventAfter);
				}
			}
		}

		if ($this->getStatusAccessor() != null) {
			$this->_statusAccessor->updateStatus($this->owner, $newStatus);
		}
		return true;
	}
	/**
	 * Creates and returns the list of events that will be fire when the owner model is sent from its current 
	 * status to the one passed as argument.
	 *
	 * The event list returned by this method depends on the event sequence component that was configured for 
	 * this behavior at construction time.
	 *
	 * @param string $status the target status
	 * @return WorkflowEvent[] The list of events
	 */
	public function getEventSequence($status)
	{
		list(,,$events) = $this->createTransitionItems($status, false, true);
		return $events;
	}
	/**
	 * Creates and returns the list of scenario names that will be used to validate the owner model when it is 
	 * sent from its current status to the one passed as argument.
	 * 
	 * @param string $status the target status
	 * @return string[] list of scenario names
	 */
	public function getScenarioSequence($status)
	{
		list( , $scenario) = $this->createTransitionItems($status, true, false);
		return $scenario;
	}

	/**
	 * Creates and returns workflow event sequence and/or scenario for the pending transition.
	 *
	 * Being given the current status and the status value passed as argument this method returns the corresponding 
	 * event sequence and the scenario names. 
	 * 
	 * The returned array contains up to 3 elements :
	 * - index = 0 : the Status instance corresponding to the $status passed as argument
	 * - index = 1 : an array of WorkflowEvents instances (the event sequence) that may contain no element if no event 
	 * sequence component is configured or if event sequence are not requested ($withEventSequence = false)
	 * - index = 2 : an array of scenario names (string) that may be empty if scenario names are not requested ($WithScenarioNames= false)
	 * 
	 * @param mixed | null $status a status Id or a Status instance considered as the target status to reach
	 * @param boolean $WithScenarioNames When TRUE scenario names are requested, FALSE otherwise
	 * @param boolean $withEventSequence When TRUE the event sequence is requested, FALSE otherwise 
	 * @throws WorkflowException
	 * @return array Three elements : Status intance, scenario names, event sequence.
	 *
	 */
	public function createTransitionItems($status, $WithScenarioNames, $withEventSequence)
	{
		$start = $this->getWorkflowStatus();
		$end = $status;

		$scenario = [];
		$events = [ 'before' => [], 'after' => []];
		$newStatus = null;
		$defaultEventCfg = null;
		
		if ( $start === null && $end !== null) {

			// (potential) entering workflow -----------------------------------

			$end = $this->ensureStatusInstance($end, true);
			$workflow = $this->_wfSource->getWorkflow($end->getWorkflowId());
			$initialStatusId = $workflow->getInitialStatusId();
			if ( $end->getId() !== $initialStatusId) {
				throw new WorkflowException('Not an initial status : '.$end->getId().' ("'.$initialStatusId.'" expected)');
			}
			if ($WithScenarioNames) {
				$scenario = [
					WorkflowScenario::enterWorkflow($end->getWorkflowId()),
					WorkflowScenario::enterStatus($end->getId())
				];
			}
			if ($withEventSequence && $this->_eventSequence !== null) {
				$events = $this->_eventSequence->createEnterWorkflowSequence($end, $this);
			}
			if( $this->fireDefaultEvent ) {
				$defaultEventCfg = [
					'end'  		 => $end,
					'sender'  	 => $this
				];
			}
			$newStatus = $end;

		} elseif ( $start !== null && $end == null) {

			// leaving workflow -------------------------------------------------

			if ($WithScenarioNames) {
				$scenario = [
					WorkflowScenario::leaveWorkflow($start->getWorkflowId()),
					WorkflowScenario::leaveStatus($start->getId())
				];
			}
			if ($withEventSequence && $this->_eventSequence !== null) {
				$events = $this->_eventSequence->createLeaveWorkflowSequence($start, $this);
			}
			if( $this->fireDefaultEvent ) {
				$defaultEventCfg = [
						'start'      => $start,
						'sender'  	 => $this
				];	
			}			
			$newStatus = $end;
		} elseif ( $start !== null && $end !== null ) {

			// change status ---------------------------------------

			$end = $this->ensureStatusInstance($end, true);
			$transition = $this->_wfSource->getTransition($start->getId(), $end->getId(), $this->selectDefaultWorkflowId()); // TODO : replace $this->owner with defaultWorkflowId
			if ( $transition === null && $start->getId() != $end->getId() ) {
				throw new WorkflowException('No transition found between status '.$start->getId().' and '.$end->getId());
			}
			if ( $transition != null) {

				if ($WithScenarioNames) {
					$scenario = [
						WorkflowScenario::leaveStatus($start->getId()),
						WorkflowScenario::changeStatus($start->getId(), $end->getId()),
						WorkflowScenario::enterStatus($end->getId())
					];
				}
				if ($withEventSequence && $this->_eventSequence !== null) {
					$events = $this->_eventSequence->createChangeStatusSequence($transition, $this);
				}
				if( $this->fireDefaultEvent ) {
					$defaultEventCfg = [
						'start'      => $transition->getStartStatus(),
						'end'  		 => $transition->getEndStatus(),
						'transition' => $transition,
						'sender'  	 => $this
					];
				}
			}
			$newStatus = $end;
		}
		
		if (count($events) != 0 && (! isset($events['before']) || ! isset($events['after']))) {
			throw new WorkflowException('Invalid event sequence format : "before" and "after" keys are mandatory');
		}
		if( $this->fireDefaultEvent && $defaultEventCfg != null) {
			array_unshift($events['before'], new WorkflowEvent(self::EVENT_BEFORE_CHANGE_STATUS, $defaultEventCfg));
			array_unshift($events['after'],  new WorkflowEvent(self::EVENT_AFTER_CHANGE_STATUS,  $defaultEventCfg));
		}
		
		return [$newStatus, $scenario, $events];
	}

	/**
	 * Returns all status that can be reached from the current status.
	 *
	 * The list of reachable statuses is returned as an array where keys are status ids and value is an associative
	 * array that contains at least the status instance. By default, no validation is performed and no event is fired by this method, however you may use
	 * $validate and $beforeEvents argument to enable them.
	 *
	 * When $validate is true, the model is validated for each scenario and for each possible transition.
	 * When $beforeEvents is true, all "before" events are fired and if a handler is attached it is executed.
	 *
	 * Each entry of the returned array has the following structure :
	 *
	 * <pre>
	 *	[
	 *	    targetStatusId => [
	 *	        'status' => the status instance
	 *	    ],
	 *      // the 'validation' key is present only if $validate is true
	 *	    'validation' => [
	 *	        0 => [
	 *              'scenario' => scenario name
	 *              'success' => true (validation success) | false (validation failure) | null (no validation for this scenario)
	 *	        ],
	 *			1 => [ ... ]
	 *		],
	 *		// the 'event' key is present only if $beforeEvent is TRUE
	 *		'event' => [
	 *			0 => [
	 *				'name' => event name
	 *				'success' => true (event handler success) | false (event handler failed : the event has been invalidated) | null (no event handler)
	 *			]
	 *			1 => [...]
	 *		],
	 *		// if $validate is true or if $beforeEvent is TRUE
	 *		'isValid' => true   (being given the verifications that were done, the target status can be reached)
	 *					| false (being given the verifications that were done, the target status cannot be reached)
	 *	]
	 * </pre>
	 *
	 *
	 * If the owner model is not currently in a workflow, this method returns the initial status of its default
	 * workflow for the model.
	 *
	 * @throws WorkflowException
	 * @return array list of status
	 */
	public function getNextStatuses($validate = false, $beforeEvents = false)
	{
		$nextStatus = [];
		if ( ! $this->hasWorkflowStatus() ) {
			$workflow = $this->_wfSource->getWorkflow($this->getDefaultWorkflowId());
			if ($workflow === null) {
				throw new WorkflowException("Failed to load default workflow ID = ".$this->getDefaultWorkflowId());
			}
			$initialStatus = $this->_wfSource->getStatus($workflow->getInitialStatusId(), $this->selectDefaultWorkflowId() );
			$nextStatus[$initialStatus->getId()] = ['status' => $initialStatus];
		} else {
			$transitions = $this->_wfSource->getTransitions($this->getWorkflowStatus()->getId(), $this->selectDefaultWorkflowId());
			foreach ($transitions as $transition) {
				$nextStatus[$transition->getEndStatus()->getId()] = [ 'status' => $transition->getEndStatus()];
			}
		}
		if ( count($nextStatus)) {

			if ( $beforeEvents ) {
				// fire before events
				foreach (array_keys($nextStatus) as $endStatusId) {
					$transitionIsValid = true;
					$eventSequence = $this->getEventSequence($endStatusId);
					foreach ( $eventSequence['before'] as $beforeEvent) {
						$eventResult = [];
						$beforeEventName = $beforeEvent->name;
						$eventResult['name'] = $beforeEventName;

						if ( $this->owner->hasEventHandlers($beforeEventName)) {
							$this->owner->trigger($beforeEventName, $beforeEvent);
							$eventResult['success'] = $beforeEvent->isValid;
							$eventResult['messages'] = $beforeEvent->getErrors();
							if ( $beforeEvent->isValid === false ) {
								$transitionIsValid = false;
							}
						} else {
							$eventResult['success'] = null;
						}
						$nextStatus[$endStatusId]['event'][] = $eventResult;
					}
					$nextStatus[$endStatusId]['isValid'] = $transitionIsValid;
				}
			}


			if ( $validate ) {
				// save scenario name and errors
				$saveScenario = $this->owner->getScenario();
				$saveErrors = $this->owner->getErrors();

				// validate
				$modelScenarios = array_keys($this->owner->scenarios());
				foreach (array_keys($nextStatus) as $endStatusId) {
					$transitionIsValid = true;
					$scenarioSequence = $this->getScenarioSequence($endStatusId);
					foreach ($scenarioSequence as $scenario) {
						$validationResult = [];

						// perform validation only if $scenario is registered for the owner model

						if ( in_array($scenario, $modelScenarios)) {
							$this->owner->clearErrors();
							$this->owner->setScenario($scenario);

							$validationResult['scenario'] = $scenario;
							if ( $this->owner->validate() == true ) {
								$validationResult['success'] = true;
							} else {
								$validationResult['success'] = false;
								$validationResult['errors'] = $this->owner->getErrors();
								$transitionIsValid = false;
							}

						} else {
							$validationResult['scenario'] = $scenario;
							$validationResult['success'] = null;
						}
						$nextStatus[$endStatusId]['validation'][] = $validationResult;
					}
					if ( isset($nextStatus[$endStatusId]['isValid'])) {
						$nextStatus[$endStatusId]['isValid'] = $nextStatus[$endStatusId]['isValid'] && $transitionIsValid;
					} else {
						$nextStatus[$endStatusId]['isValid'] = $transitionIsValid;
					}
				}
				// restore scenario name and errors
				$this->owner->setScenario($saveScenario);
				$this->owner->clearErrors();
				foreach ($saveErrors as $attributeName => $errorMessage) {
					$this->owner->addError($attributeName, $errorMessage);
				}
			}
		}
		return $nextStatus;
	}
	/**
	 * Returns the id of the default workflow associated with the owner model.
	 *
	 * If no default workflow id has been configured, it is created by using the
	 * shortname of the owner model class (i.e. the class name without the namespace part),
	 * suffixed with 'Workflow'.
	 *
	 * For instance, class 'app\model\Post' has a default workflow id equals to 'PostWorkflow'.
	 *
	 * @return string id for the workflow the owner model is in.
	 */
	public function getDefaultWorkflowId()
	{
		if ( empty($this->_defaultWorkflowId)) {
			$tokens = explode('\\', get_class($this->owner));
			$this->_defaultWorkflowId = end($tokens) . 'Workflow';
		}
		return $this->_defaultWorkflowId;
	}
	/**
	 * Returns the *Workflow Source Component* used by this behavior.
	 * This component is initialized through the [[$source]] configuration property. If not configured, the behavior creates
	 * and register its own workflow source component.
	 * 
	 * @return IWorkflowSource the workflow source component instance used by this behavior
	 */
	public function getWorkflowSource()
	{
		return $this->_wfSource;
	}
	
	/**
	 * Returns the status accessor instance used by this behavior or NULL
	 * if no status accessor is used.
	 * 
	 * @throws InvalidConfigException
	 * @return NULL|\raoul2000\workflow\base\IStatusAccessor
	 */
	public function getStatusAccessor()
	{
		if ( empty($this->statusAccessor)) {
			return null;
		}
		
		if( ! isset($this->_statusAccessor)) {
			if( is_string($this->statusAccessor)) {
				$this->_statusAccessor = Yii::$app->get($this->statusAccessor);
			}  elseif( is_object($this->statusAccessor)) {
				$this->_statusAccessor = $this->statusAccessor;
			} else {
				throw new InvalidConfigException('invalid "statusAccessor" attribute : string or object expected');
			}
			if( ! $this->_statusAccessor instanceof IStatusAccessor ) {
				throw new InvalidConfigException('the status converter must implement the IStatusAccessor interface');
			}
		}
			
		return $this->_statusAccessor;
	}
	/**
	 * Returns the status converter instance used by this behavior or NULL
	 * if no status converter is used.
	 * 
	 * @throws InvalidConfigException
	 * @return NULL|\raoul2000\workflow\base\IStatusIdConverter
	 */
	public function getStatusConverter()
	{
		if ( empty($this->statusConverter) ) {
			return null;
		}
		
		if( ! isset($this->_statusConverter)) {
			if( is_string($this->statusConverter)) {
				$this->_statusConverter = Yii::$app->get($this->statusConverter);
			}  elseif( is_object($this->statusConverter)) {
				$this->_statusConverter = $this->statusConverter;
			} else {
				throw new InvalidConfigException('invalid "statusConverter" attribute : string or object expected');
			}
			if( ! $this->_statusConverter instanceof IStatusIdConverter ) {
				throw new InvalidConfigException('the status converter must implement the IStatusConverter interface');
			}
		}
		return $this->_statusConverter;
	}	
	/**
	 * Returns the current Status instance.
	 * 
	 * @return Status the value of the status.
	 */
	public function getWorkflowStatus()
	{
		return $this->_status;
	}
	/**
	 * Returns the current Workflow instance.
	 * 
	 * @return \raoul2000\workflow\Workflow | null the workflow the owner model is currently in, or null if the owner
	 * model is not in a workflow
	 */
	public function getWorkflow()
	{
		return $this->hasWorkflowStatus() ? $this->getWorkflowSource()->getWorkflow($this->getWorkflowStatus()->getWorkflowId()) : null;
	}
	/**
	 * Returns a value indicating whether the owner model is currently in a workflow.
	 *
	 * @return boolean TRUE if the owner model is in a workflow, FALSE otherwise
	 */
	public function hasWorkflowStatus()
	{
		return $this->getWorkflowStatus() !== null;
	}
	
	/**
	 * Tests if the current status is equal to the status passed as argument.
	 * 
	 * TRUE is returned when :
	 * 
	 * - $status is empty and the owner model has no current status
	 * - $status is not empty and refers to the same status as the current one 
	 * 
	 * All other condition return FALSE.
	 * 
	 * This method can be invoked passing a string or a [[Status]] instance argument.
	 * 
	 * Example : 
	 * <pre>
	 *     $post->statusEquals('draft');
	 * 	   $post->statusEquals($otherPost->getWorkflowStatus());
	 * </pre>
	 * 
	 * @param Status|string $status the status to test
	 * @return boolean
	 */
	public function statusEquals($status=null)
	{
		if( ! empty($status)) {
			try {
				$oStatus = $this->ensureStatusInstance($status);
			}catch(Exception $e) {
				return false;
			}
		} else {
			$status = $oStatus = null;
		}
		
		if ( $oStatus == null) {
			return ! $this->hasWorkflowStatus();
		} elseif( $this->hasWorkflowStatus()) {
			return $this->getWorkflowStatus()->getId() == $oStatus->getId();
		} else {
			return false;
		}
	}
	/**
	 * Returns a Status instance for the value passed as argument.
	 *
	 * If $mixed is a Status instance, it is returned without change, otherwise $mixed is considered as a
	 * status id that is used to retrieve the corresponding status instance.
	 *
	 * @param mixed $mixed status id or status instance
	 * @param boolean $strict when TRUE and exception is thrown if no status instance can be returned.
	 * @throws WorkflowException
	 * @return Status the status instance or NULL if no Status instance could be found
	 */
	private function ensureStatusInstance($mixed, $strict = false)
	{
		if ( empty($mixed)) {
			if ( $strict ) {
				throw new WorkflowException('Invalid argument : null');
			} else {
				return null;
			}
		}elseif ( $mixed instanceof Status ) {
			return $mixed;
		} else {
			$status = $this->_wfSource->getStatus($mixed, $this->selectDefaultWorkflowId());
			if ( $status === null && $strict) {
				throw new WorkflowException('Status not found : '.$mixed);
			}
			return $status;
		}
	}
	/**
	 * Returns the value stored in the [[statusAttribute]] attribute of the owner model.
	 * If a Status Converter has been configured, it is invoked to get the status value.
	 * @return string the value of the status attribute in the owner model
	 */
	private function getOwnerStatus()
	{
		$ownerStatus = $this->owner->{$this->statusAttribute};

		if ( $this->getStatusConverter() != null) {
			$ownerStatus = $this->_statusConverter->toSimpleWorkflow($ownerStatus);
		}
		return $ownerStatus;
	}
	/**
	 * Set the internal status value and the owner model status attribute.
	 *
	 * @param Status|null $status
	 */
	private function setStatusInternal($status)
	{
		if ( $status !== null && ! $status instanceof Status) {
			throw new WorkflowException('Status instance expected');
		}

		$this->_status = $status;

		$statusId = ($status === null ? null : $status->getId());
		if ($this->getStatusConverter() != null ) {
			$statusId = $this->_statusConverter->toModelAttribute($statusId);
		}

		$this->owner->{$this->statusAttribute} = $statusId;
	}
	/**
	 * Send pending events.
	 *
	 * When the status is changed during a save operation, all the "after" events must be sent after the owner model is actually saved.
	 * This method is invoked on events ActiveRecord::EVENT_AFTER_UPDATE and ActiveRecord::EVENT_AFTER_INSERT.
	 */
	private function firePendingEvents()
	{
		if ( ! empty($this->_pendingEvents)) {
			foreach ($this->_pendingEvents as $event) {
				$this->owner->trigger($event->name, $event);
			}
			$this->_pendingEvents = [];

			if ( $this->getStatusAccessor() != null) {
				$this->_statusAccessor->commitStatus($this->owner);
			}
		}
	}
	/**
	 * Returns the default workflow ID to use with this model.
	 * The workflow ID returned is the current workflow ID (if the model is in a workflow)
	 * or the default workflow id as it has been configured.
	 * 
	 * @return string workflow Id
	 * @see getDefaultWorkflowId()
	 */
	private function selectDefaultWorkflowId()
	{
		if( $this->getWorkflowStatus() != null){
			return $this->getWorkflowStatus()->getWorkflowId();
		} else {
			return $this->getDefaultWorkflowId();
		}
	}
	/**
	 * Tests that a SimpleWorkflowBehavior behavior is attached to the object passed as argument.
	 *
	 * This method returns FALSE if $model is not an instance of BaseActiveRecord (has SimpleWorkflowBehavior can only be attached
	 * to instances of this class) or if none of its attached behaviors is a or inherit from SimpleWorkflowBehavior.
	 *
	 * @param BaseActiveRecord $model the model to test.
	 * @return boolean TRUE if at least one SimpleWorkflowBehavior behavior is attached to $model, FALSE otherwise
	 */
	public static function isAttachedTo($model)
	{
		if ( $model instanceof  yii\base\Component) {
			foreach ($model->getBehaviors() as $behavior) {
				if ($behavior instanceof SimpleWorkflowBehavior) {
					return true;
				}
			}
		} else {
			throw new WorkflowException('Invalid argument type : $model must be a BaseActiveRecord');
		}
		return false;
	}
}
