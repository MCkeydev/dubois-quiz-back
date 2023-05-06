<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503092923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_copy DROP FOREIGN KEY FK_7299C05A7D2D84D5');
        $this->addSql('DROP INDEX IDX_7299C05A7D2D84D5 ON student_copy');
        $this->addSql('ALTER TABLE student_copy DROP professor_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_copy ADD professor_id INT NOT NULL');
        $this->addSql('ALTER TABLE student_copy ADD CONSTRAINT FK_7299C05A7D2D84D5 FOREIGN KEY (professor_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7299C05A7D2D84D5 ON student_copy (professor_id)');
    }
}
