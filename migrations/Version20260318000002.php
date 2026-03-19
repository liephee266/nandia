<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les champs de profil complet à la table users.
 */
final class Version20260318000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute prenom, nom, date_naissance, telephone, sexe, situation_amoureuse, biographie, profile_image à users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS prenom VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS nom VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS date_naissance DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS sexe VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS situation_amoureuse VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS biographie TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS prenom');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS nom');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS date_naissance');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS telephone');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS sexe');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS situation_amoureuse');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS biographie');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS profile_image');
    }
}
