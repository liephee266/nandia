<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260322000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Système de badges : tables badge et user_badge';
    }

    public function up(Schema $schema): void
    {
        // ── TABLE badge ────────────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            CREATE TABLE badge (
                id              SERIAL PRIMARY KEY,
                slug            VARCHAR(50)  NOT NULL UNIQUE,
                name            VARCHAR(100) NOT NULL,
                description     TEXT         NOT NULL,
                type            VARCHAR(20)  NOT NULL DEFAULT 'sessions',
                threshold       INT          NOT NULL DEFAULT 1,
                icon_path       VARCHAR(200),
                display_order   INT          NOT NULL DEFAULT 0
            )
        SQL);

        // ── TABLE user_badge ────────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            CREATE TABLE user_badge (
                id          SERIAL PRIMARY KEY,
                user_id     INT  NOT NULL,
                badge_id    INT  NOT NULL,
                awarded_at  TIMESTAMP NOT NULL DEFAULT NOW(),
                CONSTRAINT fk_ub_user  FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_ub_badge FOREIGN KEY (badge_id) REFERENCES badge(id)  ON DELETE CASCADE,
                CONSTRAINT uq_user_badge UNIQUE (user_id, badge_id)
            )
        SQL);

        // ── Badges par défaut ──────────────────────────────────────────────
        $this->addSql(<<<'SQL'
            INSERT INTO badge (slug, name, description, type, threshold, display_order) VALUES
            ('first_session',    'Premier pas',       'Complétez votre première session.',           'sessions',  1,  1),
            ('ten_sessions',     'En forme !',          'Complétez 10 sessions.',                       'sessions', 10,  2),
            ('fifty_sessions',   'Maître du jeu',       'Complétez 50 sessions.',                       'sessions', 50,  3),
            ('first_response',   'Première réponse',     'Donnez votre première réponse.',               'responses', 1,  4),
            ('ten_responses',    'Bavard·e',            'Donnez 10 réponses.',                          'responses',10,  5),
            ('hundred_responses','Cent mots',           'Donnez 100 réponses.',                         'responses',100, 6),
            ('couple_joined',    'En couple',            'Rejoignez un couple.',                         'couple',    1,  7),
            ('room_host',        'Hôte',                 'Créez une salle multi-couples.',               'room',      1,  8),
            ('streak_3',        '3 jours de suite',     'Jouez 3 jours consécutifs.',                  'streak',    3,  9),
            ('streak_7',        'Semaine parfaite',     'Jouez 7 jours consécutifs.',                  'streak',    7, 10),
            ('streak_30',       'Mois de feu',         'Jouez 30 jours consécutifs.',                  'streak',   30, 11)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS user_badge');
        $this->addSql('DROP TABLE IF EXISTS badge');
    }
}
