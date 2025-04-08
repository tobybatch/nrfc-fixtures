<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408205409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE fixture ADD COLUMN name VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__fixture AS SELECT id, club_id, date, home_away, competition, team FROM fixture
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fixture
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE fixture (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, club_id INTEGER DEFAULT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , home_away VARCHAR(255) NOT NULL, competition VARCHAR(255) NOT NULL, team INTEGER NOT NULL, CONSTRAINT FK_5E540EE61190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO fixture (id, club_id, date, home_away, competition, team) SELECT id, club_id, date, home_away, competition, team FROM __temp__fixture
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__fixture
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5E540EE61190A32 ON fixture (club_id)
        SQL);
    }
}
