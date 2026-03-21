<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260321062425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE badge ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE badge ALTER threshold DROP DEFAULT');
        $this->addSql('ALTER INDEX badge_slug_key RENAME TO UNIQ_FEF0481D989D9B62');
        $this->addSql('ALTER TABLE card_vote ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE card_vote ALTER created_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN card_vote.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX uq_cv_voter_per_card RENAME TO uniq_vote_per_card');
        $this->addSql('ALTER TABLE couple ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE couple ALTER invite_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE couple ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE couple ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE couple ALTER activated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN couple.invite_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN couple.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN couple.activated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX uq_couple_invite_code RENAME TO UNIQ_D840B5496F21F112');
        $this->addSql('ALTER TABLE room ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE room ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE room ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE room ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE room ALTER started_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE room ALTER ended_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN room.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN room.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN room.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN room.ended_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX uq_room_code RENAME TO UNIQ_729F519B77153098');
        $this->addSql('ALTER TABLE room_participant DROP CONSTRAINT IF EXISTS uq_rp_room_couple');
        $this->addSql('DROP INDEX IF EXISTS uq_rp_room_couple');
        $this->addSql('ALTER TABLE room_participant ALTER joined_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE room_participant ALTER joined_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN room_participant.joined_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE session ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE session ALTER mode TYPE VARCHAR(20)');
        $this->addSql('COMMENT ON COLUMN session.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_sc_session_revealed');
        $this->addSql('ALTER TABLE session_card ADD favorited BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE session_card ALTER user1_responded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE session_card ALTER user2_responded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE session_card ALTER timer_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN session_card.user1_responded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN session_card.user2_responded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN session_card.timer_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE theme ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE theme ADD size VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE theme ADD background_image TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_badge ALTER awarded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE user_badge ALTER awarded_at DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN user_badge.awarded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE users ALTER refresh_token_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER refresh_token_revoked_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER refresh_token_issued_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER reset_token_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN users.refresh_token_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.refresh_token_revoked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.refresh_token_issued_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.reset_token_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX uniq_refresh_token RENAME TO UNIQ_1483A5E9C74F2195');
        $this->addSql('ALTER INDEX uniq_reset_token RENAME TO UNIQ_1483A5E9D7C8DC19');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE theme DROP description');
        $this->addSql('ALTER TABLE theme DROP size');
        $this->addSql('ALTER TABLE theme DROP background_image');
        $this->addSql('ALTER TABLE couple ALTER status SET DEFAULT \'pending\'');
        $this->addSql('ALTER TABLE couple ALTER invite_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE couple ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE couple ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('ALTER TABLE couple ALTER activated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN couple.invite_expires_at IS NULL');
        $this->addSql('COMMENT ON COLUMN couple.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN couple.activated_at IS NULL');
        $this->addSql('ALTER INDEX uniq_d840b5496f21f112 RENAME TO uq_couple_invite_code');
        $this->addSql('ALTER TABLE badge ALTER type SET DEFAULT \'sessions\'');
        $this->addSql('ALTER TABLE badge ALTER threshold SET DEFAULT 1');
        $this->addSql('ALTER INDEX uniq_fef0481d989d9b62 RENAME TO badge_slug_key');
        $this->addSql('ALTER TABLE user_badge ALTER awarded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE user_badge ALTER awarded_at SET DEFAULT \'now()\'');
        $this->addSql('COMMENT ON COLUMN user_badge.awarded_at IS NULL');
        $this->addSql('ALTER TABLE users ALTER refresh_token_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER refresh_token_revoked_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER refresh_token_issued_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE users ALTER reset_token_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN users.refresh_token_expires_at IS NULL');
        $this->addSql('COMMENT ON COLUMN users.refresh_token_revoked_at IS NULL');
        $this->addSql('COMMENT ON COLUMN users.refresh_token_issued_at IS NULL');
        $this->addSql('COMMENT ON COLUMN users.reset_token_expires_at IS NULL');
        $this->addSql('ALTER INDEX uniq_1483a5e9c74f2195 RENAME TO uniq_refresh_token');
        $this->addSql('ALTER INDEX uniq_1483a5e9d7c8dc19 RENAME TO uniq_reset_token');
        $this->addSql('ALTER TABLE session_card DROP favorited');
        $this->addSql('ALTER TABLE session_card ALTER user1_responded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE session_card ALTER user2_responded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE session_card ALTER timer_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN session_card.user1_responded_at IS NULL');
        $this->addSql('COMMENT ON COLUMN session_card.user2_responded_at IS NULL');
        $this->addSql('COMMENT ON COLUMN session_card.timer_expires_at IS NULL');
        $this->addSql('CREATE INDEX idx_sc_session_revealed ON session_card (session_id, revealed, skipped)');
        $this->addSql('ALTER TABLE room DROP updated_at');
        $this->addSql('ALTER TABLE room ALTER status SET DEFAULT \'waiting\'');
        $this->addSql('ALTER TABLE room ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE room ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('ALTER TABLE room ALTER started_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE room ALTER ended_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN room.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN room.started_at IS NULL');
        $this->addSql('COMMENT ON COLUMN room.ended_at IS NULL');
        $this->addSql('ALTER INDEX uniq_729f519b77153098 RENAME TO uq_room_code');
        $this->addSql('ALTER TABLE room_participant ALTER joined_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE room_participant ALTER joined_at SET DEFAULT \'now()\'');
        $this->addSql('COMMENT ON COLUMN room_participant.joined_at IS NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_rp_room_couple ON room_participant (room_id, couple_id)');
        $this->addSql('ALTER TABLE session DROP updated_at');
        $this->addSql('ALTER TABLE session ALTER mode TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE card_vote ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE card_vote ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('COMMENT ON COLUMN card_vote.created_at IS NULL');
        $this->addSql('ALTER INDEX uniq_vote_per_card RENAME TO uq_cv_voter_per_card');
    }
}
