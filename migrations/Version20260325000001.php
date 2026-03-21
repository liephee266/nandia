<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les colonnes de réinitialisation de mot de passe sur users.
 */
final class Version20260325000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reset_token + reset_token_expires_at to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users
                ADD COLUMN IF NOT EXISTS reset_token VARCHAR(128) DEFAULT NULL,
                ADD COLUMN IF NOT EXISTS reset_token_expires_at TIMESTAMP DEFAULT NULL
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_RESET_TOKEN ON users (reset_token)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS UNIQ_RESET_TOKEN');
        $this->addSql(<<<'SQL'
            ALTER TABLE users
                DROP COLUMN IF EXISTS reset_token,
                DROP COLUMN IF EXISTS reset_token_expires_at
        SQL);
    }
}
