<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePlansTable extends AbstractMigration
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
        // Cria a tabela tb_plans
        $table = $this->table('tb_plans');
        $table
            ->addColumn('plan_name', 'string', ['limit' => 255])
            ->addColumn('plan_description', 'text', ['null' => true])
            ->addColumn('plan_price', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('billing_period', 'enum', ['values' => ['mensal', 'trimestral', 'anual']])
            ->addColumn('accessible_modules', 'text', ['null' => true])
            ->addColumn('default_plan', 'boolean', ['default' => false])
            ->addColumn('public_plan', 'boolean', ['default' => false])
            ->addColumn('active_plan', 'boolean', ['default' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_plans
        if ($this->hasTable('tb_plans')) {
            $this->table('tb_plans')->drop()->save();
        }
    }
}
