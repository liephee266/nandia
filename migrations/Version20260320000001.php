<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les colonnes refresh_token et refresh_token_expires_at sur la table users.
 * Utilisées pour le renouvellement silencieux du JWT sans redemander les credentials.
 */
final class Version20260320000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute refresh_token et refresh_token_expires_at sur users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS refresh_token VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS refresh_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        // Index unique pour la recherche rapide par token
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_refresh_token ON users (refresh_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS UNIQ_refresh_token');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS refresh_token');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS refresh_token_expires_at');
    }
}
