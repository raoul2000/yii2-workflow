# Events

*SimpleWorkflow* is making use of [Yii2 events](http://www.yiiframework.com/doc-2.0/guide-concept-events.html) to allow customization
of model behavior.  

Basically when something interesting happens to a model inside a workflow, an event is fired, or more precisely a **sequence of events** is
fired.

## Event Sequence

The default event sequence used by the *SimpleWorkflow* behavior is the *BasicEventSequence*. Below is a list of events
fired by this sequence :

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
				<li><b>afterLeaveWorkflow{W1}</b></li></ul>
		</td>
	</tr>	
</table> 

Two other event sequences are also available in the namespace `raoul2000\workflow\events` :
- ReducedEventSequence
- ExtendedEventSequence

Of course you can create your own event sequence if the ones provided don't meet your needs. To do so, simply create a class that 
implements the `raoul2000\workflow\events\IEventSequence` interface.


## Configuration

When the *SimpleWorkflowBehavior* is attached to a model, it tries to get a reference to the **Event Sequence Component** to use. By default
this component is assumed to have the id *eventSequence*. If no such component is available, *it will create one* using the *BasicEventSequence* 
class and register it in the Yii2 application so to make it available to other *SimpleWorkflowBehavior*.

If for instance you want to use the *ReducedEventSequence* instead of the default one, you must configure it like you would do for 
any other Yii2 component.

```php
$config = [
    // ....
    'components' => [
    	// the default event sequence component is configured as ReducedEventSequence
    	// and not anymore BasicEventSequence
        'eventSequence' => [
          'class' => '\raoul2000\workflow\events\ReducedEventSequence',
        ]
   // ...
```        

The *SimpleWorkflowBehavior* will then use the *eventSequence* component created by configuration. 

You may also want to use a reduced event sequence for one particular workflow, and the basic event sequence with another one. 
This can be easely achieved by configuring the name of the event sequence component that a *SimpleWorkflowBehavior* should use.

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

Now let's tell to the behavior of the Post model that is must use the event sequence component configured above 
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

Any other model with a *SimpleWorkflowBehavior* will keep using the default Event sequence (BasicEventSequence) but the Post model
will use a specific one (ReducedEventSequence).


Usage of events is enabled by default but can be disabled by setting the *eventSequence* configuration parameter to NULL. In this case 
as you may expect, no event is fired.

```php
<?php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
			'eventSequence' => null	// disable all SimpleWorkflow events
    	];
    }
}
```

## Event Handler
A event handler can be used to implement specific process on any of these events.  
