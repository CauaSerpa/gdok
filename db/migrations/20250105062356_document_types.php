<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DocumentTypes extends AbstractMigration
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
        $table = $this->table('tb_document_types');
        $table
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('category_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('advance_notification', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('personalized_advance_notification', 'integer')
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_document_types
        if ($this->hasTable('tb_document_types')) {
            $this->table('tb_document_types')->drop()->save();
        }
    }
}
