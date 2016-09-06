# Workflow Events

*yii2-workflow* is making use of [Yii2 events](http://www.yiiframework.com/doc-2.0/guide-concept-events.html) to allow you to customize the behavior of your models while they are evolving inside workflows.

Basically when *something interesting happens* to a model inside a workflow, an event is fired, or more precisely a **sequence of events** is fired.

## Event Sequence

The default event sequence used by the *SimpleWorkflow* behavior is the *BasicEventSequence* available in the namespace `\raoul2000\workflow\events`. Below is a list of events fired by this sequence :

<table width="100%">
	<tr>
		<td><b>workflow event</b></td>
		<td><b>Basic Event Sequence</b></td>
	</tr>
	<tr>
		<td>the model enters in workflow W1</td>
		<td>
			<ul>
				<li><b>beforeEnterWorkflow{W1}</b></li>
				<li><b>beforeEnterStatus{W1/init}</b> where 'init' is the initial status Id for the workflow W1</li>
				<li><b>afterEnterWorkflow{W1}</b></li>
				<li><b>afterEnterStatus{W1/init}</b> where 'init' is the initial status Id for the workflow W1</li>
			</ul>
		</td>
	</tr>
	<tr>
		<td>the model goes from status W1/A to W1/B</td>
		<td>
			<ul>
				<li><b>beforeLeaveStatus{W1/A}</b></li>
				<li><b>beforeChangeStatusFrom{W1/A}to{W1/B}</b></li>
				<li><b>beforeEnterStatus{W1/B}</b></li>
				<li><b>afterLeaveStatus{W1/A}</b></li>
				<li><b>afterChangeStatusFrom{W1/A}to{W1/B}</b></li>
				<li><b>afterEnterStatus{W1/B}</b></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td>the model leaves the workflow W1</td>
		<td>
			<ul>
				<li><b>beforeLeaveStatus{W1/B}</b> where W1/B is the last status Id of the model before it leaves the workflow</li>
				<li><b>beforeLeaveWorkflow{W1}</b></li>
				<li><b>afterLeaveStatus{W1/B}</b> where W1/B is the last status Id of the model before it leaves the workflow</li>
				<li><b>afterLeaveWorkflow{W1}</b></li>
			</ul>
		</td>
	</tr>
</table>

Two other event sequences are also available in the same namespace :

- ReducedEventSequence
- ExtendedEventSequence

Of course you can create your own event sequence if the ones provided don't meet your needs. To do so, simply create a class that
implements the `raoul2000\workflow\events\IEventSequence` interface (see below).


## Configuration

When the *SimpleWorkflowBehavior* is initialized, it tries to get a reference to the **Event Sequence Component** to use. By default
this component is assumed to have the id *eventSequence*. If no such component is available, *it will create one* using the *BasicEventSequence*
class and register it in the Yii2 application so to make it available to other *SimpleWorkflowBehavior*.

To summarize :

- **eventSequence** : default Id of the event sequence component used by the *SimpleWorkflowBehavior*
- **BasicEventSequence** : default event sequence type

If for instance you want to use the *ReducedEventSequence* instead of the default one, you must configure it like you would do for
any other Yii2 component.

```php
$config = [
    // ....
    'components' => [
    	// the default event sequence component is configured as a ReducedEventSequence
    	// and not a BasicEventSequence anymore
        'eventSequence' => [
          'class' => '\raoul2000\workflow\events\ReducedEventSequence',
        ]
   // ...
```        

The *SimpleWorkflowBehavior* will then use the configured *eventSequence* component.

You may also want to use a reduced event sequence for one particular workflow, and the basic event sequence with another ones.
This can be easely achieved by configuring the ID of the event sequence component that a *SimpleWorkflowBehavior* should use.

In the example below we are first configuring a new component with the ID *myReducedEventSequence* and with type *ReducedEventSequence*.

```php
$config = [
    // ....
    'components' => [
        'myReducedEventSequence' => [
          'class' => '\raoul2000\workflow\events\ReducedEventSequence',
        ]
   // ...
```  

Now let's tell to the behavior of the Post model that it must use the *myReducedEventSequence* event sequence component configured above
instead of the default one.

```php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
    		[
				'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
				'eventSequence' => 'myReducedEventSequence'
			]
    	];
    }
}
```

Any other model with a *SimpleWorkflowBehavior* will keep using the default Event sequence (*BasicEventSequence*) but the Post model
will use a specific one (*ReducedEventSequence*).

*SimpleWorkflow* events are enabled by default but can be disabled by setting the *eventSequence* configuration parameter to NULL when
attaching the behavior to the model. In this case as you may expect, no event is fired for this model.

```php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			[
				'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
				'eventSequence' => null	// disable all SimpleWorkflow Events for Post instances
			]
    	];
    }
}
```

## Event Object

All events fired are instances of the `raoul2000\workflow\events\WorkflowEvent` class which provides all the method needed to get informations
on the event that just occured.

- `getStartStatus()` : returns the Status instance that the model is leaving. If the WorkflowEvent is fired because the model *enters* into a workflow, this method returns `null`.
- `getEndStatus()` : returns the Status instance that the model is reaching. If the WorkflowEvent is fired because the model *leaves* a workflow, this method returns `null`.
- `getTransition()` : returns the Transition instance that the model is performing. Note that if the WorkflowEvent is fired because the model *enters* or *leaves* the
workflow, this method returns `null`.

Remember that a `WorkflowEvent` object is passed to all attached handlers(see next chapter).

## Event Handler

A event handler is used to implement a specific process on any of the events in the event sequence. Installing an event handler is
a standard operation described in the [Yii2 Definitive Guide](http://www.yiiframework.com/doc-2.0/guide-concept-events.html#attaching-event-handlers).

In the example below, we are attaching an handler for the event that is fired when a Post instance goes from the status *draft* to the
status *correction*. When this happens, we have decided to send a mail.

```php
use raoul2000\workflow\events\WorkflowEvent;

class Post extends \yii\db\ActiveRecord
{
	public function init()
	{
		$this->on(
			'afterChangeStatusFrom{PostWorkflow/draft}to{PostWorkflow/correction}',
			[$this, 'sendMail']
		);
	}
	// $event is an instance of raoul2000\workflow\events\WorkflowEvent
	public function sendMail($event)
	{
		MailingService::sendMailToCorrector(
			'A Post is ready for correction',
			'The post [' . $event->sender->owner->title . '] is ready to be corrected.'
		);		
	}
```


## before vs after

Each event fired by the *SimpleWorkflowBehavior* can be of 2 types : **before** or **after**. The difference between these types is
that a handler attached to a *before* event is able to block the transition in progress by *invalidating* the event. This is not possible
for a handler attached to a "after* event. Using this feature it becomes possible to block a transition based on the result of a complex
processing.

In the following example, an event handler is attached to be invoked *before* a Post instance enters into the status 'Post/published'.
This handler checks that the user who is performing the action has the appropriate permission and if not it *invalidates* the event : the
Post instance will not be able to reach the status 'W1/A', the transition is blocked.

```php
use raoul2000\workflow\events\WorkflowEvent;

class Post extends \yii\db\ActiveRecord
{
	public function init()
	{
		$this->on(
			'beforeEnterStatus{Post/published}',
			function ($event) {
				// if the user doesn't have the current authorization, the transition to 'Post/published' is blocked

				if( \Yii::$app->user->can('publish.post') == false) {
					$event->invalidate("you don't have permission to publish this post");
				}
			}
		);
	}
	// .....
```

Event handlers attached to *before* events allow you to authorize or forbid a transition based on the result of a custom code execution.


### Status Constraint

If you have been using the previous version of *SimpleWorkflow* (only compatible with Yii 1.x) you may be familiar with *Status Constraint*.
A *Status Constraint* is a piece of PHP code associated with a status and evaluated as a logical
expression **before a model enters into this status** : if the evaluation succeeds, the model can enter the status
otherwise the transition is blocked and the model remains in its current status ([read more](http://s172418307.onlinehome.fr/project/sandbox/www/index.php?r=simpleWorkflow/page&view=doc#constraint)).

*Status Constraint* are **not declared anymore as PHP code inside the workflow definition** like in version 1.x but as event handlers
attached to the *before* event type, just like in the previous example where in fact, we have defined a constraint on the status *W1/A*.

Remember that an event handler attached to a *before* event type is able to block the transition by invalidating the event object, and that's
exactly what a *Status Constraint* does !

### Workflow Tasks

Just like *Status Constraint* above, *Workflow Task* is a feature available with the previous version of *SimpleWorkflow*.
To summarize, a workflow task is a piece of PHP code attached to a transition and executed when the transition
is performed by the model ( [read more](http://s172418307.onlinehome.fr/project/sandbox/www/index.php?r=simpleWorkflow/page&view=doc#tasks)).

Such feature must now be implemented as an event handler attached to an *after* event and not anymore as PHP code defined in the workflow
definition (like it used to be in version 1.x). In a [previous chapter](#event-handler) we have already created a workflow task by
attaching a handler to the event *afterChangeStatusFrom{PostWorkflow/draft}to{PostWorkflow/correction}* : a mail is sent when the model
goes from `draft` to `correction`.


## Getting The Event Sequence

Once *SimpleWorkflowBehavior* is attached to model, it injects several method that you can use directly from a model instance (this is
[standard Yii2 feature](http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html#using-behaviors)). Among these methods,
`getEventSequence()` is particularly useful when working with events. This method returns
an array describing all the events that will be fired if the model is sent to the status passed as argument. This array has 2 keys :
*before* and *after*. The value of each key is an array of `raoul2000\workflow\events\WorkflowEvent` objects representing the event
that will be fired before and after the transition.

Let's see that on the example below where we assume that the `$post` instance in currently in status *ready*. This snippet
is displaying the ordered list of event that will be fired when `$post` is sent to status *published*.

```php
// $post is assumed to be in status 'ready'
foreach ($post->getEventSequence('published') as $type => $events) {
	foreach($events as $event) {
		echo 'type = '.$type. ' event name = '.$event->name.'<br/>';
	}
}
```

The transition we are working on is from *ready* to *published*. The event sequence used is the default one and here is
the output which matches the *BasicEventSequence* specifications.

	type = before event name = beforeLeaveStatus{PostWorkflow/ready}
	type = before event name = beforeChangeStatusFrom{PostWorkflow/ready}to{PostWorkflow/published}
	type = before event name = beforeEnterStatus{PostWorkflow/published}
	type = after event name = afterLeaveStatus{PostWorkflow/ready}
	type = after event name = afterChangeStatusFrom{PostWorkflow/ready}to{PostWorkflow/published}
	type = after event name = afterEnterStatus{PostWorkflow/published}
Remember that events will be fired in this exact order until the last event or until the event is invalidated by a handler attached
to the *before* events.

## Event Name Helper

The class `\raoul2000\workflow\events\WorkflowEvent` includes a set of static method that you can use to easely create workflow event names.
It's even more useful if your favorite IDE supports auto-completion ! The example below is equivalent to the previous one except that
the event name is created at runtime by a call to `WorkflowEvent::beforeEnterStatus('W1/A')`.

```php
$this->on(
	// use the event helper to generate the event name
	WorkflowEvent::beforeEnterStatus('W1/A'),
	function ($event) {
		// ...
	}
);
```

## Creating An Event Sequence

We already know that *SimpleWorkflow* includes 3 event sequences, from the most simple to the most *verbose* one (the default is the
`BasicEventSequence`). However, you may want to create your own event sequence if for instance you want to optimize the amount of events
fired and actually handled by your implementation.

To define your own event sequence you must create a class that implements the `\raoul2000\workflow\events\IEventSequence` interface.
There are three methods declared in this interface, each one being invoked at runtime, when a specific event occurs in the workflow :

- *createEnterWorkflowSequence* : invoked when a model enters into a workflow
- *createLeaveWorkflowSequence* : invoked when a model leaves a workflow
- *createChangeStatusSequence* : invoked when a model changes status

Each method must return an array representing the corresponding sequence of events, grouped in 2 possibles types : *before* and
*after* events. These types are used as keys in the returned array, and values is an array of `WorkflowEvent` objects representing
the sequence of events.

For example :

```php
public function createEnterWorkflowSequence($initalStatus, $sender)
{
	return [
		'before' => [
			new WorkflowEvent(WorkflowEvent::beforeEnterWorkflow($initalStatus->getWorkflowId()),
				['end' => $initalStatus,'sender'=> $sender]
			),
			new WorkflowEvent(WorkflowEvent::beforeEnterStatus($initalStatus->getId()),
				['end' => $initalStatus,'sender'=> $sender]
			)
		],
		'after' => [
			new WorkflowEvent(WorkflowEvent::afterEnterWorkflow($initalStatus->getWorkflowId()),
				['end' => $initalStatus,'sender' => $sender]
			),
			new WorkflowEvent(WorkflowEvent::afterEnterStatus($initalStatus->getId()),
				['end' => $initalStatus,'sender' => $sender]
			)
		]
	];
}
```

## Generic Events

We have seen in the previous chapters that using an *Event Sequence* you can easely implement a custom behavior for your model evolving into a workflow, by installing
the appropriate event handlers: an *Event Sequence* allows you to react to the exact event you need with a thin control. However, if you don't need such precision, you can
also use so called *generic events*.

Two *Generic Events* are always fired by the *SimpleWorkflowBehavior* as soon as a model changes status, and this, no matter what *Event Sequence* is configured. In fact
even if you choose to not use *Event Sequence*, the *Generic Events* are fired, because they are fired by the *SimpleWorkflowBehavior* itself, and not by another component (like
the event sequence component).

The names of the 2 *Generic Events* are :

- EVENT_BEFORE_CHANGE_STATUS : fired each time **before** a model changes status
- EVENT_AFTER_CHANGE_STATUS : fired each time **after** a model changes status

The *before* and *after* event type follow the same rules as with *Event Sequence* and in particular you can block a transition by invalidating the *before* event.

In order to identify exactly what just happened to your model inside the workflow, you must test what are the values of the *startStatus* and *endStatus* by calling the
corresponding `WorkflowEvent` methods.

- `getStartStatus() == null && getEndStatus() != null` : the model is **entering** into the workflow
- `getStartStatus() =! null && getEndStatus() != null` : the model is **changing** status
- `getStartStatus() == null && getEndStatus() == null` : the model is **leaving** into the workflow
