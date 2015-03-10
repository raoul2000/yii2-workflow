# Concepts

Before being able to create a workflow there are some simple key conecpts that you must know.

## Identifiers

The *SimpleWorkflow* behavior refers to workflow and statuses using Ids. 

 
## Initial Status

The initial status is the first status assigned to a model, that's the *entry point* into a workflow. Each
workflow must have exactly one initial status.

For example, in a workflow dedicated to manage posts, the initial status could be called 'draft' or 'new' : it usually
describes the first state of the post. 


## Transition

A transition is a *directed* link between two statuses : the *start* status and the *end* status (I also use the words
'source' and 'target' status).

For example, if we define a transition between the status 'draft' and 'published', a post with status 'draft' is able to
reach status 'published', but not the opposite.

  

