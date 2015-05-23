<?php

namespace tests\codeception\unit\models;

use raoul2000\workflow\source\file\IWorkflowDefinitionProvider;

class Item05Workflow implements IWorkflowDefinitionProvider
{
	public function getDefinition()
	{
		return [
			'initialStatusId' => 'new',
			'status' => [
				'new' => [
					'label' => 'New Item',
					'transition' => [
						'correction' => [],
						'published' => []
					]
				],
				'correction' => [
					'label' => 'In Correction',
					'transition' => [
						'published' => []
					]
				],
				'published' => [
					'label' => 'Published',
					'transition' => [
						'correction' => [],
						'archive' => []
					]
				],
				'archive' => [
					'label' => 'Archived',
					'transition' => []
				]
			]
		];
	}
}