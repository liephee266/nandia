<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Crée la table favorite_card (favoris utilisateur).
 */
final class Version20260326000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create favorite_card table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS favorite_card (
                id          SERIAL PRIMARY KEY,
                user_id     INT NOT NULL,
                card_id     INT NOT NULL,
                created_at  TIMESTAMP NOT NULL DEFAULT NOW(),
                CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                CONSTRAINT fk_fav_card FOREIGN KEY (card_id) REFERENCES card (id)  ON DELETE CASCADE,
                CONSTRAINT uq_user_card_favorite UNIQUE (user_id, card_id)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS favorite_card');
    }
}
