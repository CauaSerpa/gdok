<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AdminUser extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Inserir dados na tabela tb_users
        $dataAdminUser = [
            [
                'role'          => 0,
                'firstname'     => 'Admin',
                'lastname'      => '001',
                'email'         => 'admin@admin.com',
                'password'      => '$2y$10$bsVxUdsFhORHKzyvcgZLWuiu8rlfRqWT9/2h6WZ.cPvYRL7ld/Z.G',
                'active_status' => 1,
            ]
        ];
        $this->table('tb_users')
            ->insert($dataAdminUser)
            ->save();
 
    }
}
