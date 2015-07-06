# Migrating From *SimpleWorkflow* 1.x

If you have been using the [previous version of *SimpleWorkflow*](http://s172418307.onlinehome.fr/project/sandbox/www/index.php?r=simpleWorkflow/page&view=home) 
together with Yii 1.x and now want to migrate to Yii2, this chapter is for you. We will focus on the main differences between the
previous version of *SimpleWorkflow* (v1.x) and this one (v2.x).

Main improvements in v2.x aimed to clearly separate the workflow definition in terms of status and transition, from the business logic
that implements the way a model is going to behave inside a workflow. By doing so, the same workflow can be easily used by models of different
types, each one having its own behavior.  

Another goal was to provide maximum flexibility in the way the developer is going to setup its app architecture. For instance the workflow definition
can be embeded in any PHP class that implements the appropriate interface (`raoul2000\workflow\source\file\IWorkflowDefinitionProvider`). Moreover almost
all classes can be overloaded and new ones created to implements specific needs not covered by the current version. 


## Principles

Not that much to say here, as there is no change in the way the *SimpleWorkflowBehavior* detects status changes :

- On one side an Active Record attribute which can be viewed as the future status. 
- On the other side, the actual status managed internally by the behavior.

A transition is a directed link between to statuses : the *start* and the *end* status.  

## Definition
The workflow definition required by the `WorkflowFileSource` component differs from previous version : 

- key `initial` is replaced by `initialStatusId`
- key `node` is replaced by `status`
- status ids are stored as keys of the *status* array (and not as value of the `id` key anymore)

For more information please refer to [Workflow File Source Component](source-file.md) documentation. 

## Workflow Tasks

### Declaration

Workflow Tasks are not declared anymore in the workflow definition itself. This could create a dependency between the workflow and the 
model, lie for instance when the workflow tasks was using *$this*. 

### Implementation

Workflow task used to be a piece of PHP code associated with a workflow transition and evaluated when this transition was performed.
With this new version, **Workflow Tasks should be implemented as event handler attached to a *after* event type**. 

Please refer to the [Workflow Event](concept-events.md) documentation for more.

## Status Constraints
### Declaration
Status Constraints are not declared anymore in the workflow definition itself, for the same reason as above.

### Implementation
Status Constraints used to be a piece of PHP code associated with a status and evaluated as a logical expression *before* a model enters 
into this status. If the evaluation returned TRUE, the model can access the status, otherwise the transition is blocked.

The same principles still applies but in v2.x **status constraints should be implemented as event handler attached to a *before* event type.**

Please refer to the [Workflow Event](concept-events.md) documentation for more.

## Workflow Driven Validation

It is still possible to validate models attributes based on the transition that is done by the model. The principle remains the same : 
Workflow driven validation is based on dynamic scenario names and their associated validation rules declared in the model.

Please refer to the [Workflow Driven Attribute Validation](concept-validation.md) documentation for more.

## Events

There are no major changes in the way workflow events are managed although the event model has been enhanced to provide more control
through event handlers.

Please refer to the [Workflow Event](concept-events.md) documentation for more.
 