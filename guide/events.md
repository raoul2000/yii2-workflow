# Events

*SimpleWorkflow* is making use of [Yii2 events](http://www.yiiframework.com/doc-2.0/guide-concept-events.html) to allow customization
of model behavior.  

Basically when something interesting happens to a model inside a workflow, an event is fired, or more precisely a **sequence of events** is
fired.

## Event Sequence

The default event sequence used by the *SimpleWorkflow* behavior is the *BasicEventSequence* available in the namespace 
`\raoul2000\workflow\events`. Below is a list of events fired by this sequence :

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
<?php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
			'eventSequence' => 'myReducedEventSequence'
    	];
    }
}
```

Any other model with a *SimpleWorkflowBehavior* will keep using the default Event sequence (*BasicEventSequence*) but the Post model
will use a specific one (*ReducedEventSequence*).

*SimpleWorkflow* events are enabled by default but can be disabled by setting the *eventSequence* configuration parameter to NULL when
attaching the behavior to the model. In this case as you may expect, no event is fired for this model.

```php
<?php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
			'eventSequence' => null	// disable all SimpleWorkflow Events for Post instances
    	];
    }
}
```

## Event Handler

A event handler is used to implement specific process on any of the events in the event sequence. Installing an event handler is
a standard operation described in the [Yii2 Definitive Guide](http://www.yiiframework.com/doc-2.0/guide-concept-events.html#attaching-event-handlers). 

In the example below, we are attaching an handler for the event that is fired when a Post instance goes from the status *draft* to the
status *correction*. 

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

In the example below, an event handler is attached to be invoked *before* a Post instance enters into the status 'W1/A'. 
This handler checks that the user who is performing the action has the appropriate permission and if not it *invalidates* the event : the
Post instance will not be able to reach the status 'W1/A', the transition is blocked.

```php
use raoul2000\workflow\events\WorkflowEvent;

class Post extends \yii\db\ActiveRecord
{
	public function init()
	{
		$this->on(
			'beforeEnterStatus{W1/A}',
			function ($event) {
				// if the user doesn't have the current authorization, the transition to 'W1/A' is blocked
				$event->isValid = \Yii::$app->user->can('do.action');
			}
		);
	}	
	// .....
``` 

Event handlers attached to *before* events allow you to authorize or forbid a transition based on the result of a custom code execution.

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

## Event Name Helper

The class *\raoul2000\workflow\events\WorkflowEvent* includes a set of static method that you can use to easely create workflow event names.
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

  