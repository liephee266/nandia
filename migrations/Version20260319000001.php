<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute :
 * - colonne `roles` (JSON) sur `users` pour supporter ROLE_ADMIN
 * - colonne `theme_id` (FK nullable) sur `session` pour lier une session à un thème choisi
 */
final class Version20260319000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute roles sur users et theme_id sur session';
    }

    public function up(Schema $schema): void
    {
        // Colonne roles sur users (JSON, vide par défaut = simple utilisateur)
        $this->addSql("ALTER TABLE users ADD COLUMN IF NOT EXISTS roles JSON NOT NULL DEFAULT '[]'");

        // FK theme_id sur session (nullable : les sessions "random" n'ont pas de thème)
        $this->addSql('ALTER TABLE session ADD COLUMN IF NOT EXISTS theme_id INT DEFAULT NULL');
        $this->addSql("
            DO \$\$ BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'fk_session_theme'
                ) THEN
                    ALTER TABLE session ADD CONSTRAINT fk_session_theme
                        FOREIGN KEY (theme_id) REFERENCES theme(id) ON DELETE SET NULL;
                END IF;
            END \$\$
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session DROP CONSTRAINT IF EXISTS fk_session_theme');
        $this->addSql('ALTER TABLE session DROP COLUMN IF EXISTS theme_id');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS roles');
    }
}
