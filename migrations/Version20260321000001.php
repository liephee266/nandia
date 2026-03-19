<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration — Révocabilité du refresh token
 *
 * Ajoute :
 *  - users.refresh_token_revoked_at  (datetime nullable)
 *  - users.refresh_token_issued_at    (datetime nullable)
 *
 * Permet d'invalider un refresh token après un logout explicite,
 * sans avoir à attendre son expiration naturelle (30j).
 */
final class Version20260321000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Révocabilité du refresh token : ajout de refresh_token_revoked_at et refresh_token_issued_at sur users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users
                ADD COLUMN IF NOT EXISTS refresh_token_revoked_at TIMESTAMP,
                ADD COLUMN IF NOT EXISTS refresh_token_issued_at  TIMESTAMP
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE users
                DROP COLUMN IF EXISTS refresh_token_revoked_at,
                DROP COLUMN IF EXISTS refresh_token_issued_at
        SQL);
    }
}
