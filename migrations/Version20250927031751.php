<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927031751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('CREATE TABLE card (id SERIAL NOT NULL, theme_id INT NOT NULL, question_text TEXT NOT NULL, difficulty_level SMALLINT DEFAULT NULL, is_bonus BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_161498D359027487 ON card (theme_id)');
        $this->addSql('COMMENT ON COLUMN card.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE pack (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, price NUMERIC(6, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN pack.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE response (id SERIAL NOT NULL, user_id INT NOT NULL, session_card_id INT NOT NULL, answer_text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3E7B0BFBA76ED395 ON response (user_id)');
        $this->addSql('CREATE INDEX IDX_3E7B0BFB5814187A ON response (session_card_id)');
        $this->addSql('COMMENT ON COLUMN response.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE ritual (id SERIAL NOT NULL, theme_id INT DEFAULT NULL, title VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, type VARCHAR(20) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ED8FF2B059027487 ON ritual (theme_id)');
        $this->addSql('COMMENT ON COLUMN ritual.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE session (id SERIAL NOT NULL, user_id INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, mode VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D044D5D4A76ED395 ON session (user_id)');
        $this->addSql('COMMENT ON COLUMN session.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN session.ended_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE session_card (id SERIAL NOT NULL, session_id INT NOT NULL, card_id INT NOT NULL, drawn_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, skipped BOOLEAN DEFAULT false NOT NULL, order_index INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D06598F9613FECDF ON session_card (session_id)');
        $this->addSql('CREATE INDEX IDX_D06598F94ACC9A20 ON session_card (card_id)');
        $this->addSql('COMMENT ON COLUMN session_card.drawn_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE theme (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, icon VARCHAR(50) DEFAULT NULL, color_code VARCHAR(7) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, pseudo VARCHAR(100) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D359027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB5814187A FOREIGN KEY (session_card_id) REFERENCES session_card (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ritual ADD CONSTRAINT FK_ED8FF2B059027487 FOREIGN KEY (theme_id) REFERENCES theme (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE session_card ADD CONSTRAINT FK_D06598F9613FECDF FOREIGN KEY (session_id) REFERENCES session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE session_card ADD CONSTRAINT FK_D06598F94ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE "user"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudo VARCHAR(50) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, uuid VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649d17f50a6 ON "user" (uuid)');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE card DROP CONSTRAINT FK_161498D359027487');
        $this->addSql('ALTER TABLE response DROP CONSTRAINT FK_3E7B0BFBA76ED395');
        $this->addSql('ALTER TABLE response DROP CONSTRAINT FK_3E7B0BFB5814187A');
        $this->addSql('ALTER TABLE ritual DROP CONSTRAINT FK_ED8FF2B059027487');
        $this->addSql('ALTER TABLE session DROP CONSTRAINT FK_D044D5D4A76ED395');
        $this->addSql('ALTER TABLE session_card DROP CONSTRAINT FK_D06598F9613FECDF');
        $this->addSql('ALTER TABLE session_card DROP CONSTRAINT FK_D06598F94ACC9A20');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE pack');
        $this->addSql('DROP TABLE response');
        $this->addSql('DROP TABLE ritual');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE session_card');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE users');
    }
}
