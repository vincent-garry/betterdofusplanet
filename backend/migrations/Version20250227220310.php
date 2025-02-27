<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227220310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quest_step ADD quest_id INT NOT NULL');
        $this->addSql('ALTER TABLE quest_step ADD CONSTRAINT FK_4DB352CE209E9EF4 FOREIGN KEY (quest_id) REFERENCES quest (id)');
        $this->addSql('CREATE INDEX IDX_4DB352CE209E9EF4 ON quest_step (quest_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quest_step DROP FOREIGN KEY FK_4DB352CE209E9EF4');
        $this->addSql('DROP INDEX IDX_4DB352CE209E9EF4 ON quest_step');
        $this->addSql('ALTER TABLE quest_step DROP quest_id');
    }
}
