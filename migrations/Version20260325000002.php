<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne device_token (FCM) sur users.
 */
final class Version20260325000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add device_token (FCM) to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users
                ADD COLUMN IF NOT EXISTS device_token VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS device_token');
    }
}
