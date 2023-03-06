<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306095506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student_answer (id INT AUTO_INCREMENT NOT NULL, student_copy_id INT NOT NULL, question_id INT NOT NULL, annotation VARCHAR(255) NOT NULL, score INT NOT NULL, INDEX IDX_54EB92A54543928A (student_copy_id), INDEX IDX_54EB92A51E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE student_answer ADD CONSTRAINT FK_54EB92A54543928A FOREIGN KEY (student_copy_id) REFERENCES student_copy (id)');
        $this->addSql('ALTER TABLE student_answer ADD CONSTRAINT FK_54EB92A51E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_answer DROP FOREIGN KEY FK_54EB92A54543928A');
        $this->addSql('ALTER TABLE student_answer DROP FOREIGN KEY FK_54EB92A51E27F6BF');
        $this->addSql('DROP TABLE student_answer');
    }
}
