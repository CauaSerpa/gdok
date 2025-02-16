<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Office extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Cria a tabela tb_offices
        $table = $this->table('tb_offices');
        $table
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('document', 'string', ['limit' => 18, 'null' => true]) // CPF ou CNPJ com máscara
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['document'], ['unique' => true])
            ->create();

        // Cria a tabela tb_office_addresses
        $table = $this->table('tb_office_addresses');
        $table
            ->addColumn('office_id', 'integer', ['null' => false])
            ->addColumn('type', 'enum', ['values' => ['headquarters', 'branch'], 'null' => false, 'default' => 'headquarters'])
            ->addColumn('cep', 'string', ['limit' => 9, 'null' => false])
            ->addColumn('address', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('number', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('province', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('complement', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('state', 'string', ['limit' => 2, 'null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Cria a tabela tb_office_users
        $table = $this->table('tb_office_users');
        $table
            ->addColumn('office_id', 'integer')
            ->addColumn('user_id', 'integer')
            ->addColumn('role', 'enum', ['values' => ['owner', 'manager', 'employee'], 'null' => false, 'default' => 'employee'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_offices
        if ($this->hasTable('tb_offices')) {
            $this->table('tb_offices')->drop()->save();
        }

        // Exclui a tabela tb_office_addresses
        if ($this->hasTable('tb_office_addresses')) {
            $this->table('tb_office_addresses')->drop()->save();
        }

        // Exclui a tabela tb_office_users
        if ($this->hasTable('tb_office_users')) {
            $this->table('tb_office_users')->drop()->save();
        }
    }
}
