<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetsTable extends AbstractMigration
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
        $table = $this->table('tb_password_resets', ['id' => false, 'primary_key' => 'token']);
        $table->addColumn('token', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('expiration_date', 'datetime', ['null' => false])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['token'], ['unique' => true])
              ->addIndex(['email'])
              ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_password_resets
        if ($this->hasTable('tb_password_resets')) {
            $this->table('tb_password_resets')->drop()->save();
        }
    }
}
