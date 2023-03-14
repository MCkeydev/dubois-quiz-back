<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314102500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_answer ADD choice_id INT DEFAULT NULL, ADD answer VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE student_answer ADD CONSTRAINT FK_54EB92A5998666D1 FOREIGN KEY (choice_id) REFERENCES answer (id)');
        $this->addSql('CREATE INDEX IDX_54EB92A5998666D1 ON student_answer (choice_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_answer DROP FOREIGN KEY FK_54EB92A5998666D1');
        $this->addSql('DROP INDEX IDX_54EB92A5998666D1 ON student_answer');
        $this->addSql('ALTER TABLE student_answer DROP choice_id, DROP answer');
    }
}
