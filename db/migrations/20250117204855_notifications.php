<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Notifications extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $table = $this->table('tb_notifications');
        $table
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('message', 'text', ['null' => false])
            ->addColumn('notification_type', 'enum', ['values' => ['document', 'document_expiration', 'system', 'custom'], 'null' => false])
            ->addColumn('related_id', 'integer', ['null' => true])
            ->addColumn('is_read', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('read_in', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['user_id'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_notifications
        if ($this->hasTable('tb_notifications')) {
            $this->table('tb_notifications')->drop()->save();
        }
    }
}
