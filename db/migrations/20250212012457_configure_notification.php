<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ConfigureNotification extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Cria a tabela tb_office_notification_settings
        $table = $this->table('tb_office_notification_settings');
        $table
            ->addColumn('office_id', 'integer', ['null' => false])
            ->addColumn('channel', 'enum', ['values' => ['email', 'whatsapp'], 'null' => false])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('contact', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('send_type', 'enum', ['values' => ['once_due', 'daily_until_due', 'daily_until_after', 'predefined_dates', 'due_date', 'personalized'], 'default' => 'once_due', 'null' => false])
            ->addColumn('start_days_before', 'enum', ['values' => ['once', 'daily'], 'null' => true])
            ->addColumn('after_due_days', 'integer', ['default' => 7, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Exclui a tabela tb_office_notification_settings
        if ($this->hasTable('tb_office_notification_settings')) {
            $this->table('tb_office_notification_settings')->drop()->save();
        }
    }
}
