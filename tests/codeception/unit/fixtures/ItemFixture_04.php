<?php
namespace tests\codeception\unit\fixtures;

use yii\test\ActiveFixture;

class ItemFixture_04 extends ActiveFixture
{
    public $modelClass = 'tests\codeception\unit\models\Item_04';
    public $dataFile = '@tests/codeception/unit/fixtures/data/item_04.php';
}