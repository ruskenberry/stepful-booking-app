<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718205549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE new_session (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            start_date_time DATETIME NOT NULL,
            coach_id INTEGER NOT NULL,
            student_id INTEGER DEFAULT NULL,
            satisfaction INTEGER DEFAULT NULL,
            notes CLOB DEFAULT NULL,
            status BOOLEAN DEFAULT FALSE,
            CONSTRAINT FK_D044D5D43C105691 FOREIGN KEY (coach_id) REFERENCES coach (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
            CONSTRAINT FK_D044D5D4CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('INSERT INTO new_session SELECT * FROM session');
        $this->addSql('DROP TABLE session');
        $this->addSql('ALTER TABLE new_session RENAME TO session');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE new_session (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            start_date_time DATETIME NOT NULL,
            coach_id INTEGER NOT NULL,
            student_id INTEGER DEFAULT NULL,
            satisfaction INTEGER DEFAULT NULL,
            notes CLOB DEFAULT NULL,
            status BOOLEAN NOT NULL DEFAULT FALSE,
            CONSTRAINT FK_D044D5D43C105691 FOREIGN KEY (coach_id) REFERENCES coach (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
            CONSTRAINT FK_D044D5D4CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        )');
        $this->addSql('INSERT INTO new_session SELECT * FROM session');
        $this->addSql('DROP TABLE session');
        $this->addSql('ALTER TABLE new_session RENAME TO session');
    }
}
