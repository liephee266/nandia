<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Supprime la colonne plain_password de la table users.
 * Ce champ ne doit jamais être persisté en base de données.
 */
final class Version20260318000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime la colonne plain_password de la table users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS plain_password');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN plain_password TEXT DEFAULT NULL');
    }
}
