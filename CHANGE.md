#version 0.0.4
- add tests for raoul2000\workflow\source\php\DefaultArrayParser
- minor fix in DefaultArrayParser

#version 0.0.3
- externalize array parser to normalize workflow php array definition

#version 0.0.2
- change regex for status and workflow ID pattern: now `/^[a-zA-Z]+[[:alnum:]-]*$/`
- Improve WorkflowPhpSource parsing of a workflow defined as a PHP array.

```php
[
	'initialStatusId' => 'A',
	'status' => [
		'A' => [
			'transition' => 'A,  B'
		],
		'B' => [
			'transition' => ['A','C']
		],
		'C'
	]
]
```

# version 0.0.1
- Initial import