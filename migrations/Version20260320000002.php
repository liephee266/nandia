<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration — Jeu en couple & multi-couples
 *
 * Crée :
 *  - couple              (lien entre deux utilisateurs)
 *  - room                (salle de jeu multi-couples)
 *  - room_participant    (couples dans une salle + score)
 *  - card_vote           (vote communautaire en salle)
 *
 * Modifie :
 *  - session             (mode, couple_id, room_id, card_count, timer_per_card)
 *  - session_card        (réponses couple, revealed, current_turn, timer_expires_at)
 */
final class Version20260320000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Jeu en couple et multi-couples : couple, room, room_participant, card_vote, modifications session & session_card';
    }

    public function up(Schema $schema): void
    {
        // ── TABLE couple ────────────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS couple (
                id                  SERIAL PRIMARY KEY,
                user1_id            INT NOT NULL,
                user2_id            INT,
                status              VARCHAR(20)  NOT NULL DEFAULT 'pending',
                invite_code         VARCHAR(10)  NOT NULL,
                invite_expires_at   TIMESTAMP,
                created_at          TIMESTAMP    NOT NULL DEFAULT NOW(),
                activated_at        TIMESTAMP,
                CONSTRAINT fk_couple_user1 FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_couple_user2 FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT uq_couple_invite_code UNIQUE (invite_code)
            )
        SQL);

        // ── TABLE room ──────────────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS room (
                id                      SERIAL PRIMARY KEY,
                host_couple_id          INT          NOT NULL,
                code                    VARCHAR(10)  NOT NULL,
                status                  VARCHAR(20)  NOT NULL DEFAULT 'waiting',
                max_couples             INT          NOT NULL DEFAULT 8,
                card_count              INT          NOT NULL DEFAULT 10,
                timer_per_card          INT,
                theme_id                INT,
                current_card_index      INT          NOT NULL DEFAULT 0,
                current_card_id         INT,
                current_session_card_id INT,
                card_phase              VARCHAR(20)  NOT NULL DEFAULT 'answering',
                created_at              TIMESTAMP    NOT NULL DEFAULT NOW(),
                started_at              TIMESTAMP,
                ended_at                TIMESTAMP,
                CONSTRAINT fk_room_host_couple         FOREIGN KEY (host_couple_id)          REFERENCES couple(id)       ON DELETE CASCADE,
                CONSTRAINT fk_room_theme               FOREIGN KEY (theme_id)                REFERENCES theme(id)        ON DELETE SET NULL,
                CONSTRAINT fk_room_current_card        FOREIGN KEY (current_card_id)         REFERENCES card(id)         ON DELETE SET NULL,
                CONSTRAINT fk_room_current_session_card FOREIGN KEY (current_session_card_id) REFERENCES session_card(id) ON DELETE SET NULL,
                CONSTRAINT uq_room_code UNIQUE (code)
            )
        SQL);

        // ── TABLE room_participant ───────────────────────────────────────────
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS room_participant (
                id                          SERIAL PRIMARY KEY,
                room_id                     INT  NOT NULL,
                couple_id                   INT  NOT NULL,
                score                       INT  NOT NULL DEFAULT 0,
                joined_at                   TIMESTAMP NOT NULL DEFAULT NOW(),
                has_answered_current_card   BOOLEAN   NOT NULL DEFAULT FALSE,
                current_answer              TEXT,
                CONSTRAINT fk_rp_room   FOREIGN KEY (room_id)   REFERENCES room(id)   ON DELETE CASCADE,
                CONSTRAINT fk_rp_couple FOREIGN KEY (couple_id) REFERENCES couple(id) ON DELETE CASCADE,
                CONSTRAINT uq_rp_room_couple UNIQUE (room_id, couple_id)
            )
        SQL);

        // ── TABLE card_vote ──────────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS card_vote (
                id                  SERIAL PRIMARY KEY,
                session_card_id     INT  NOT NULL,
                voter_couple_id     INT  NOT NULL,
                target_couple_id    INT  NOT NULL,
                created_at          TIMESTAMP NOT NULL DEFAULT NOW(),
                CONSTRAINT fk_cv_session_card   FOREIGN KEY (session_card_id)  REFERENCES session_card(id) ON DELETE CASCADE,
                CONSTRAINT fk_cv_voter_couple   FOREIGN KEY (voter_couple_id)  REFERENCES couple(id)       ON DELETE CASCADE,
                CONSTRAINT fk_cv_target_couple  FOREIGN KEY (target_couple_id) REFERENCES couple(id)       ON DELETE CASCADE,
                CONSTRAINT uq_cv_voter_per_card UNIQUE (session_card_id, voter_couple_id)
            )
        SQL);

        // ── ALTER session ────────────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            ALTER TABLE session
                ADD COLUMN IF NOT EXISTS couple_id      INT,
                ADD COLUMN IF NOT EXISTS room_id        INT,
                ADD COLUMN IF NOT EXISTS card_count     INT,
                ADD COLUMN IF NOT EXISTS timer_per_card INT
        SQL);

        // Migrer l'ancien champ mode VARCHAR(50) nullable → VARCHAR(20) NOT NULL DEFAULT 'solo'
        $this->addSql(<<<'SQL'
            UPDATE session SET mode = 'solo' WHERE mode IS NULL OR mode = ''
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session
                ALTER COLUMN mode SET NOT NULL,
                ALTER COLUMN mode SET DEFAULT 'solo'
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE session
                ADD CONSTRAINT fk_session_couple FOREIGN KEY (couple_id) REFERENCES couple(id) ON DELETE SET NULL,
                ADD CONSTRAINT fk_session_room   FOREIGN KEY (room_id)   REFERENCES room(id)   ON DELETE SET NULL
        SQL);

        // ── ALTER session_card ───────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            ALTER TABLE session_card
                ADD COLUMN IF NOT EXISTS user1_response      TEXT,
                ADD COLUMN IF NOT EXISTS user1_responded_at  TIMESTAMP,
                ADD COLUMN IF NOT EXISTS user2_response      TEXT,
                ADD COLUMN IF NOT EXISTS user2_responded_at  TIMESTAMP,
                ADD COLUMN IF NOT EXISTS revealed            BOOLEAN NOT NULL DEFAULT FALSE,
                ADD COLUMN IF NOT EXISTS current_turn        INT,
                ADD COLUMN IF NOT EXISTS timer_expires_at    TIMESTAMP
        SQL);

        // ── ALTER card — ajout du champ type ────────────────────────────────
        $this->addSql(<<<'SQL'
            ALTER TABLE card
                ADD COLUMN IF NOT EXISTS type VARCHAR(20) NOT NULL DEFAULT 'question'
        SQL);

        // Index pour accélérer le polling sur session_card
        $this->addSql(<<<'SQL'
            CREATE INDEX IF NOT EXISTS idx_sc_session_revealed
                ON session_card (session_id, revealed, skipped)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session DROP CONSTRAINT IF EXISTS fk_session_couple');
        $this->addSql('ALTER TABLE session DROP CONSTRAINT IF EXISTS fk_session_room');
        $this->addSql('ALTER TABLE session DROP COLUMN IF EXISTS couple_id');
        $this->addSql('ALTER TABLE session DROP COLUMN IF EXISTS room_id');
        $this->addSql('ALTER TABLE session DROP COLUMN IF EXISTS card_count');
        $this->addSql('ALTER TABLE session DROP COLUMN IF EXISTS timer_per_card');

        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS user1_response');
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS user1_responded_at');
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS user2_response');
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS user2_responded_at');
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS revealed');
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS current_turn');
        $this->addSql('ALTER TABLE session_card DROP COLUMN IF EXISTS timer_expires_at');
        $this->addSql('DROP INDEX IF EXISTS idx_sc_session_revealed');
        $this->addSql('ALTER TABLE card DROP COLUMN IF EXISTS type');

        $this->addSql('DROP TABLE IF EXISTS card_vote');
        $this->addSql('DROP TABLE IF EXISTS room_participant');
        $this->addSql('DROP TABLE IF EXISTS room');
        $this->addSql('DROP TABLE IF EXISTS couple');
    }
}
