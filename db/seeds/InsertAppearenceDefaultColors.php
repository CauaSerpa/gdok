<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class InsertAppearenceDefaultColors extends AbstractSeed
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
        // Inserir dados na tabela tb_template_appearance
        $dataTemplateAppearence = [
            [
                'bg_color'           => '#F8F9FA',
                'header_color'       => '#ffffff',
                'sidebar_color'      => '#ffffff',
                'text_color'         => '#4a5a6b',
                'button_color'       => '#287F71',
                'hover_color'        => '#247266',
            ]
        ];
        $this->table('tb_template_appearance')
             ->insert($dataTemplateAppearence)
             ->save();

    }
}
