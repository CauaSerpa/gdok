<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnStatus extends AbstractMigration
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
        $table = $this->table('tb_users');
        $table
            ->addColumn('status', 'integer', ['default' => 1, 'null' => false, 'after' => 'id'])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Remover as colunas da tabela tb_users
        $table = $this->table('tb_users');
        $table
            ->removeColumn('status')
            ->update();
    }
}
