<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260321175813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CASCADE DELETE on session_card.session + performance indexes';
    }

    public function up(Schema $schema): void
    {
        // CASCADE DELETE : supprimer les session_cards quand la session est supprimée
        $this->addSql('ALTER TABLE session_card DROP CONSTRAINT FK_D06598F9613FECDF');
        $this->addSql('ALTER TABLE session_card ADD CONSTRAINT FK_D06598F9613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Index sur session_card.session_id (requêtes par session)
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_session_card_session ON session_card (session_id)');

        // Index sur couple (status, created_at) pour les requêtes de codes en attente
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_couple_status_created ON couple (status, created_at DESC)');

        // Index sur room_participant.couple_id
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_room_participant_couple ON room_participant (couple_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IF EXISTS idx_session_card_session');
        $this->addSql('DROP INDEX IF EXISTS idx_couple_status_created');
        $this->addSql('DROP INDEX IF EXISTS idx_room_participant_couple');
        $this->addSql('ALTER TABLE session_card DROP CONSTRAINT fk_d06598f9613fecdf');
        $this->addSql('ALTER TABLE session_card ADD CONSTRAINT fk_d06598f9613fecdf FOREIGN KEY (session_id) REFERENCES session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
