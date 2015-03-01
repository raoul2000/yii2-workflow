#version 0.0.2
- change regex for status and workflow ID pattern: now `/^[a-zA-Z]+[[:alnum:]-]*$/`
- WorkflowPhpSource now accept transition definition as comma separated list. For example :

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
		'C' => []
	]
]
```

Note that even if status "**C**" has no out going transition, it MUST be defined as an array key.
 
# version 0.0.1
- Initial import