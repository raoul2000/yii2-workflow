<?php

namespace tests\codeception\unit\models;

use raoul2000\workflow\base\IWorkflowDefinitionProvider;

class Item_04Workflow implements IWorkflowDefinitionProvider
{
	public function getDefinition()
	{
		return [
			'initialStatusId' => 'A',
			'status' => [
				'A' => [
					'label' => 'Entry',
					'transition' => [
						'B' => [],
						'A' => []
					],
					'metadata' => [
						'color' => '#FF545669',
						'priority' => 1
					]
				],
				'B' => [
					'label' => 'Published',
					'transition' => [
						'A' => [],
						'C' => []
					]
				],
				'C' => [
					'label' => 'node C',
					'transition' => [
						'A' => [],
						'D' => []
					]
				],
				'D' => [
					'label' => 'node D',
					'transition' => []
				]
			]
		];
	}
}