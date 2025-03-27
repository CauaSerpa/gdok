<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTemplateAppearanceTable extends AbstractMigration
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
        // Cria a tabela tb_template_appearance
        $table = $this->table('tb_template_appearance');
        $table
            ->addColumn('bg_color', 'string', ['limit' => 7])
            ->addColumn('header_color', 'string', ['limit' => 7])
            ->addColumn('sidebar_color', 'string', ['limit' => 7])
            ->addColumn('text_color', 'string', ['limit' => 7])
            ->addColumn('button_color', 'string', ['limit' => 7])
            ->addColumn('hover_color', 'string', ['limit' => 7])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_template_appearance
        if ($this->hasTable('tb_template_appearance')) {
            $this->table('tb_template_appearance')->drop()->save();
        }
    }
}
