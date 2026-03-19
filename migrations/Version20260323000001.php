<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ difficulty sur room (niveau de difficulté des cartes)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE room
                ADD COLUMN difficulty SMALLINT NULL
                DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE room
                DROP COLUMN difficulty
        SQL);
    }
}
