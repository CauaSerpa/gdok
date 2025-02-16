<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Documents extends AbstractMigration
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
        $table = $this->table('tb_documents');
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('company_id', 'integer')
            ->addColumn('document_type_id', 'integer')
            ->addColumn('document', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('expiration_date', 'date', ['null' => false])
            ->addColumn('advance_notification', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('personalized_advance_notification', 'integer', ['null' => true])
            ->addColumn('observation', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_documents
        if ($this->hasTable('tb_documents')) {
            $this->table('tb_documents')->drop()->save();
        }
    }
}
