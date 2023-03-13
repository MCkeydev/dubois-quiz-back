<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230313101217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_copy ADD evaluation_id INT NOT NULL');
        $this->addSql('ALTER TABLE student_copy ADD CONSTRAINT FK_7299C05A456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id)');
        $this->addSql('CREATE INDEX IDX_7299C05A456C5646 ON student_copy (evaluation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_copy DROP FOREIGN KEY FK_7299C05A456C5646');
        $this->addSql('DROP INDEX IDX_7299C05A456C5646 ON student_copy');
        $this->addSql('ALTER TABLE student_copy DROP evaluation_id');
    }
}
