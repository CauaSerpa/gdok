<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Users extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Cria a tabela tb_users
        $table = $this->table('tb_users', ['identity' => true, 'signed' => false]);
        $table
            ->addColumn('firstname', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('role', 'integer')
            ->addColumn('lastname', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('active_status', 'boolean', ['default' => 0, 'null' => false])
            ->addColumn('active_token', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('document', 'string', ['limit' => 18, 'null' => true]) // CPF ou CNPJ com máscara
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['document'], ['unique' => true])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_users
        if ($this->hasTable('tb_users')) {
            $this->table('tb_users')->drop()->save();
        }
    }
}
