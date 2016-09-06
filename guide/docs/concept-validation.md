# Workflow Driven Attribute Validation

The *SimpleWorkflow* behavior provides a way to apply a specific set of validation rules to model attributes, depending on the
way the model is *moving* inside a workflow. This allows you for instance to apply a set of validation rules only when the model
goes from a specific status to another one.

## Principles

The *Workflow Driven Attribute Validation* is making use of standard Yii2 features : "[User Input Validation](http://www.yiiframework.com/doc-2.0/guide-input-validation.html)"
and "[Scenario](http://www.yiiframework.com/doc-2.0/guide-structure-models.html#scenarios)".


To implement *Workflow Driven Attribute Validation* you must :

1. declare the `WorkflowValidator` validation rule for the attribute used to store the status (by default *status*)
2. declare the validation rules you need and set the *workflow scenario name* for which they should be applied.

The *Workflow Scenario Name* is a formatted string identitying the event that occurs on the model inside its workflow.
Below is a list of currently supported scenario names :

<table width="100%">
	<tr>
		<td><b>Scenario name template</b></td>
		<td><b>Description</b></td>
	</tr>
	<tr>
		<td>`from {W1/S1} to {W1/S2}`</td>
		<td>the model goes from status S1 to status S2 in workflow W1</td>
	</tr>
	<tr>
		<td>`leave status {W1/S1}`</td>
		<td>the model leaves the status S1 in workflow W1</td>
	</tr>
	<tr>
		<td>`enter status {W1/S1}`</td>
		<td>the model enters into the status S1 in workflow W1</td>
	</tr>
	<tr>
		<td>`enter workflow {W1}`</td>
		<td>the model enters into workflow W1</td>
	</tr>
	<tr>
		<td>`leave workflow {W1}`</td>
		<td>the model leaves workflow W1</td>
	</tr>
</table>

As you can see, scenario names are quite self explanatory ! To assist you in using workflow scenario names, you can use the class `raoul2000\workflow\validation\WorkflowScenario`.

For instance :

```php
echo WorkflowScenario::changeStatus('W1/S1','W1/S2'); 	// "from {W1/S1} to {W1/S2}"
echo WorkflowScenario::leaveStatus('W1/S1'); 			// "leave status {W1/S1}"
echo WorkflowScenario::enterStatus('W1/S1'); 			// "enter status {W1/S1}"
echo WorkflowScenario::enterWorkflow('W1'); 			// "enter workflow {W1}"
echo WorkflowScenario::leaveWorkflow('W1'); 			// "leave workflow {W1}"
```

## Usage example

In the example below we are defining several validation rules applied to the model during its life-cycle through the workflow
it is assigned to.

```php
use raoul2000\workflow\validation\WorkflowValidator;
/**
 * @property integer $id
 * @property string $col_status
 * @property string $title
 * @property string $body
 * @property string $category
 * @property string $tags
 */
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	// declare the SimpleWorkflowBehavior.
        return [
        	'workflow' => [
        		'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
        		'defaultWorkflowId'      => 'post',
        		'statusAttribute'        => 'col_status',
        		'propagateErrorsToModel' => true
    	    ]
        ];
    }

    public function rules()
    {
        return [
        	[['col_status'],raoul2000\workflow\validation\WorkflowValidator::className()],

        	// rule 1 :  the 'title' is always required
        	['title','required'],

        	// rule 2 : the 'body' is required when the post is about to enter to 'post/correction'
        	[['body'],'required',
        		'on' => 'enter status {post/correction}'],

        	// rule 3 : 'category' is set during correction and before publication
        	['category', 'required',
        		'on' => 'from {post/correction} to {post/published}'

        	// rule 4 : 'tags' and 'category' are required before being published or archived.
        	[['tags', 'category'], 'required',
        		'on' => ['enter status {post/published}', 'enter status {post/archived}']
        	],        	
        ];
    }
```

In the above example we have defined a *Post* model and configured validation rules to implement the following (imaginary) business rules :

- **rule n°1 : a post must always have a title** : that's a standard one, no workflow magic here
- **rule n°2 : redactors are not allowed to send a empty post to correction** : attribute `body` is required when the post enters into status *post/correction*
- **rule n°3 : correctors are responsible for setting a category to all post before publishing it** : when a post leaves the 'post/correction* status to go to
*post/published* it must have the `category`attribute set.
- **rule n°4 : it is forbidden to publish or archive a post with no tags or no category** : attributes `tags` and `category` cannot be empty when the post
enter into status *post/published* or *post/archived*.



## Implementation

So how does this works ? As you may have guessed, the entry point is the `WorkflowValidator` validator configured for the `status` attribute.
This validator is not actually going to validate the attribute it is configured for ! Yes, believe it or not, this validator doesn't care about the current status value or if the model is about to perform a transition that is not permitted : validating transitions is done by the `SimpleWorkflowBehavior` itself, when the model is saved (or when you explicitly invoke `sendToStatus()` on the model) and not by the `WorkflowValidator` validator.

When a model is validated, following occurs :

- `WorkflowValidator` identifies the pending transition by looking at the `status` attribute value and the current Status.
- Based on the pending transition, get a *scenario sequence*
- for each scenario in the scenario sequence, apply corresponding validating rules
