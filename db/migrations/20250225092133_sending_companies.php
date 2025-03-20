<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SendingCompanies extends AbstractMigration
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
        $table = $this->table('tb_sending_companies');
        $table
            ->addColumn('user_id', 'integer')
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('logo', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('responsible', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_sending_companies
        if ($this->hasTable('tb_sending_companies')) {
            $this->table('tb_sending_companies')->drop()->save();
        }
    }
}
