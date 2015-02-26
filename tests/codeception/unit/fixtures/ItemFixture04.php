<?php
namespace tests\codeception\unit\fixtures;

use yii\test\ActiveFixture;

class ItemFixture04 extends ActiveFixture
{
    public $modelClass = 'tests\codeception\unit\models\Item04';
    public $dataFile = '@tests/codeception/unit/fixtures/data/item04.php';
}