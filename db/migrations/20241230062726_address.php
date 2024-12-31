<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Address extends AbstractMigration
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

    /**
     * Migrate Up.
     */
    public function up()
    {
        // Cria a tabela tb_address
        $addressesTable = $this->table('tb_address');
        $addressesTable
            ->addColumn('user_id', 'integer')
            ->addColumn('street', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('number', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('state', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('postal_code', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('country', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_address
        if ($this->hasTable('tb_address')) {
            $this->table('tb_address')->drop()->save();
        }
    }
}
