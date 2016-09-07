## Generalities

*yii2-workflow* is a set on objects dedicated to help managing the life-cycle of an ActiveRecord model within a *workflow*.

It includes :

- a behavior (*SimpleWorkflowBehavior*)
- a Workflow Source Component (*WorkflowFileSource*)
- a Validator (*WorkflowValidator*)
- three event sequence models
- various helpers
- a set of interfaces

*yii2-workflow* can be configure to fit your requirements and if that's not enough, you can extend all classes so to implement your own features.

## Identifiers

The *yii2-workflow* refers to workflows and statuses using identifiers. The way these identifiers are formatted, depends on the *WorkflowSource* components used. For instance if you're working with the default source component (the *workflowFileSource* ), status identifiers will look like this : `workflowId/StatusId` ([read more](workflow-creation/#identifiers))

## Initial Status

The initial status is the first status assigned to a model, that's the *one and only entry point* into a workflow. Each workflow must have exactly one initial status.

For example, in a workflow dedicated to manage posts, the initial status could be called 'draft' : it usually describes the first state of the post.

## Transition

A transition is a *directed* link between two statuses : the *start* status and the *end* status (the words 'source' and 'target' may also be used).

For example, if we define a transition between the status 'draft' and 'published', a post with status 'draft' (the start status) is able to reach status 'published' (the end status), but not the opposite.

## Workflow Source

The *Workflow Source* is a component responsible for providing workflow, status and transitions objects based on a formatted workflow definition.

A *Workflow Source* component can ready virtually any kind of source. The first release includes the `WorkflowFileSource` component : by default this source reads a workflow definition from a PHP array wrapped in a class.

[Read more about Workflow Source](concept-source.md)

## Events

The *SimpleWorkflow* is making use of [Yii2 events](http://www.yiiframework.com/doc-2.0/guide-concept-events.html) to allow customization of model behavior. You can attach handlers to these events in order to implement a specific behavior to your model during its life cycle inside the workflow.

[Read more about events](concept-events.md)
