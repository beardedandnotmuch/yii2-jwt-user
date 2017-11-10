<?php

use yii\db\Migration;

class m171110_141203_create_table_user_destroyed_tokens extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_destroyed_tokens}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'token_hash' => $this->string()->unique(),
            'expired_at' => $this->datetime(),
            'created_at' => $this->datetime(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_user_destroyed_tokens_to_users',
            '{{%user_destroyed_tokens}}', 'user_id',
            '{{%users}}', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_user_destroyed_tokens_to_users', '{{%user_destroyed_tokens}}');
        $this->dropTable('{{%user_destroyed_tokens}}');
    }
}
