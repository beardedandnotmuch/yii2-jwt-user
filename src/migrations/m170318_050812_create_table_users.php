<?php

use yii\db\Migration;

class m170318_050812_create_table_users extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'status' => $this->integer(),
            'email' => $this->string()->unique()->notNull(),
            'password_hash' => $this->string(),
            'confirm_token_hash' => $this->string(),
            'reset_password_token_hash' => $this->string(),
            'rate_limit' => $this->integer()->notNull()->defaultValue(180),
            'allowance' => $this->integer(),
            'allowance_updated_at' => $this->datetime(),
            'created_at' => $this->datetime(),
            'updated_at' => $this->datetime(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%users}}');
    }
}
