<?php

use yii\db\Migration;

/**
 * Handles the creation of table `adres`.
 */
class m170731_064409_create_adres_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('adres', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->comment('用户id'),
            'name'=>$this->string()->comment('姓名'),
            'province'=>$this->string()->comment('省'),
            'city'=>$this->string()->comment('市'),
            'area'=>$this->string()->comment('区/县'),
            'detail'=>$this->string()->comment('详细地址'),
            'tel'=>$this->string()->comment('电话号码'),
            'status'=>$this->integer(2)->comment('设为默认地址')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('adres');
    }
}
