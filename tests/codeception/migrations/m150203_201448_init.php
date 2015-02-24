<?php

use yii\db\Schema;
use yii\db\Migration;

class m150203_201448_init extends Migration
{
    public function up()
    {	
		 $this->createTable('item', [
			'id' => Schema::TYPE_PK,
			'name' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'status' => Schema::TYPE_STRING . ' DEFAULT NULL'
			], 
			'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
		);
    }

    public function down()
    {
		$this->dropTable('item');
    }
}
