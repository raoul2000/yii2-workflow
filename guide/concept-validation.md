# Workflow Driven Attribute Validation

The *SimpleWorkflow* behavior provides a way to apply a specific set of validation rules to model attributes, depending on the 
way the model is *moving* inside a workflow. This allows you for instance to apply a set of validation rules only when the model 
goes from a specific status to another one.

## Principles

The *Workflow Driven Attribute Validation* is making use of standard Yii2 features : "[User Input Validation](http://www.yiiframework.com/doc-2.0/guide-input-validation.html)"
and "[Scenario](http://www.yiiframework.com/doc-2.0/guide-structure-models.html#scenarios).


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
		<td>`from {S1} to {S2}`</td>
		<td>the model goes from status S1 to status S2</td>
	</tr>
	<tr>
		<td>`leave status {S1}`</td>
		<td>the model leaves the status S1</td>
	</tr>
	<tr>
		<td>`enter status {S1}`</td>
		<td>the model enters into the status S1</td>
	</tr>
	<tr>
		<td>`enter workflow {W}`</td>
		<td>the model enters into workflow W</td>
	</tr>
	<tr>
		<td>`leave workflow {W}`</td>
		<td>the model leaves workflow W</td>
	</tr>
</table>

As you can see, scenario names are quite self explanatory !

## Usage example

In the example below we are defined several validation rules applied to the model during its life-cycle through the workflow 
it is assigned to.

```php
use raoul2000\workflow\validation\WorkflowValidator;

class Post extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
        	[['status'],raoul2000\workflow\validation\WorkflowValidator::className()],
        	['name','required',
        		'on' => 'from {Post3Workflow/draft} to {Post3Workflow/correction}'],        	
        ];
    }	
```






