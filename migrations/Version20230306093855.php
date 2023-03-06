<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306093855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, quiz_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', starts_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ends_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_locked TINYINT(1) NOT NULL, INDEX IDX_1323A575F675F31B (author_id), INDEX IDX_1323A575853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student_copy (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, professor_id INT NOT NULL, can_share TINYINT(1) NOT NULL, commentary VARCHAR(255) DEFAULT NULL, average_score INT DEFAULT NULL, INDEX IDX_7299C05ACB944F1A (student_id), INDEX IDX_7299C05A7D2D84D5 (professor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE student_copy ADD CONSTRAINT FK_7299C05ACB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE student_copy ADD CONSTRAINT FK_7299C05A7D2D84D5 FOREIGN KEY (professor_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575F675F31B');
        $this->addSql('ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575853CD175');
        $this->addSql('ALTER TABLE student_copy DROP FOREIGN KEY FK_7299C05ACB944F1A');
        $this->addSql('ALTER TABLE student_copy DROP FOREIGN KEY FK_7299C05A7D2D84D5');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE student_copy');
    }
}
