<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227214110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quest ADD steps_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quest ADD CONSTRAINT FK_4317F8171EBBD054 FOREIGN KEY (steps_id) REFERENCES quest_step (id)');
        $this->addSql('CREATE INDEX IDX_4317F8171EBBD054 ON quest (steps_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quest DROP FOREIGN KEY FK_4317F8171EBBD054');
        $this->addSql('DROP INDEX IDX_4317F8171EBBD054 ON quest');
        $this->addSql('ALTER TABLE quest DROP steps_id');
    }
}
