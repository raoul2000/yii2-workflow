<?php

namespace tests\codeception\unit\workflow\models;

use Yii;
use yii\codeception\TestCase;
use app\models\LoginForm;
use Codeception\Specify;

class LoginFormTest extends TestCase
{
    use Specify;

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testDummy()
    {
        $this->specify('dummy test always succeeds', function ()  {
            expect('true is true', true)->true();
        });
    }
}
