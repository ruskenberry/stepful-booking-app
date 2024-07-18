<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718210254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       $this->addSql('DROP TABLE session');
       $this->addSql('CREATE TABLE session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, coach_id INTEGER NOT NULL, student_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, satisfaction INTEGER DEFAULT NULL, notes CLOB DEFAULT NULL, status BOOLEAN DEFAULT TRUE, CONSTRAINT FK_D044D5D43C105691 FOREIGN KEY (coach_id) REFERENCES coach (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D044D5D4CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
   }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__session AS SELECT id, coach_id, student_id, start_date_time, status, satisfaction, notes FROM session');
        $this->addSql('DROP TABLE session');
        $this->addSql('CREATE TABLE session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, coach_id INTEGER NOT NULL, student_id INTEGER DEFAULT NULL, start_date_time DATETIME NOT NULL, status BOOLEAN DEFAULT TRUE, satisfaction INTEGER DEFAULT NULL, notes CLOB DEFAULT NULL, CONSTRAINT FK_D044D5D43C105691 FOREIGN KEY (coach_id) REFERENCES coach (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D044D5D4CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO session (id, coach_id, student_id, start_date_time, status, satisfaction, notes) SELECT id, coach_id, student_id, start_date_time, status, satisfaction, notes FROM __temp__session');
        $this->addSql('DROP TABLE __temp__session');
        $this->addSql('CREATE INDEX IDX_D044D5D43C105691 ON session (coach_id)');
        $this->addSql('CREATE INDEX IDX_D044D5D4CB944F1A ON session (student_id)');
    }
}
