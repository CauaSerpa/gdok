<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NotifyClient extends AbstractMigration
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
        $table = $this->table('tb_companies');
        $table
            ->addColumn('notify_phone', 'boolean', ['default' => false, 'null' => false, 'after' => 'phone'])
            ->addColumn('notify_email', 'boolean', ['default' => false, 'null' => false, 'after' => 'email'])
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Remover as colunas da tabela tb_companies
        $table = $this->table('tb_companies');
        $table
            ->removeColumn('notify_phone')
            ->removeColumn('notify_email')
            ->update();
    }
}
