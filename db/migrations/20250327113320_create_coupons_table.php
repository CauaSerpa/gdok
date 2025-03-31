<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCouponsTable extends AbstractMigration
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
        // Cria a tabela tb_coupons
        $table = $this->table('tb_coupons');
        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('validity_start', 'date')
            ->addColumn('validity_end', 'date')
            ->addColumn('discount_type', 'enum', ['values' => ['fixed', 'percentage']])
            ->addColumn('discount_value', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('accessible_modules', 'text', ['null' => true])
            ->addColumn('code', 'string', ['limit' => 100])
            ->addTimestamps()
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_coupons
        if ($this->hasTable('tb_coupons')) {
            $this->table('tb_coupons')->drop()->save();
        }
    }
}
