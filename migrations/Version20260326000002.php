<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne note (texte libre) sur session_card.
 */
final class Version20260326000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add note column to session_card';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE session_card
                ADD COLUMN IF NOT EXISTS note TEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS note');
    }
}
