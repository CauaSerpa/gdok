<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SendingCompanyFields extends AbstractMigration
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
            ->addColumn('channels', 'string', ['limit' => 255, 'null' => true, 'after' => 'cidade'])
            ->addColumn('use_same_email_envios', 'string', ['limit' => 255, 'null' => true, 'after' => 'channels'])
            ->addColumn('email_envios', 'string', ['limit' => 255, 'null' => true, 'after' => 'use_same_email_envios'])
            ->addColumn('use_same_whatsapp_envios', 'string', ['limit' => 255, 'null' => true, 'after' => 'email_envios'])
            ->addColumn('whatsapp_envios', 'string', ['limit' => 255, 'null' => true, 'after' => 'use_same_whatsapp_envios'])
            ->addColumn('portal_envios', 'string', ['limit' => 255, 'null' => true, 'after' => 'whatsapp_envios'])
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
            ->removeColumn('channels')
            ->removeColumn('use_same_email_envios')
            ->removeColumn('email_envios')
            ->removeColumn('use_same_whatsapp_envios')
            ->removeColumn('whatsapp_envios')
            ->removeColumn('portal_envios')
            ->update();
    }
}