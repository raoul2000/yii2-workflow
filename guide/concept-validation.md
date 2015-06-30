# Workflow Driven Attribute Validation

The *SimpleWorkflow* behavior provides a way to apply a specific set of attribute validation rules, depending on the 
way the model is *moving* inside a workflow. This allows you for instance to apply a set of validation rules only when the model 
goes from a specific status to another one.

The *Workflow Driven Attribute Validation* is making use of standard Yii2 features : "[User Input Validation](http://www.yiiframework.com/doc-2.0/guide-input-validation.html)"
and "[Scenario](http://www.yiiframework.com/doc-2.0/guide-structure-models.html#scenarios).


To implement *Workflow Driven Attribute Validation* you must :

1. declare the `WorkflowValidator` validation rule for the attribute used to store the status (by default *status*)
2. declare the validation rules you need and set the *workflow scenario name* for which they should be applied.

The *Workflow Scenario Name* is a formatted string identitying the event that occurs on the model inside its workflow.





