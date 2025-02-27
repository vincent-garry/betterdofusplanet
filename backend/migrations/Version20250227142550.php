<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227142550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dofus (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, quest_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dofus_quest (id INT AUTO_INCREMENT NOT NULL, dofus_id INT DEFAULT NULL, quest_id INT DEFAULT NULL, quest_order INT NOT NULL, INDEX IDX_C5C213EF4368DD8C (dofus_id), INDEX IDX_C5C213EF209E9EF4 (quest_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quest (id INT AUTO_INCREMENT NOT NULL, steps_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, level INT DEFAULT NULL, INDEX IDX_4317F8171EBBD054 (steps_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quest_quest (quest_source INT NOT NULL, quest_target INT NOT NULL, INDEX IDX_10AA618BA6601D85 (quest_source), INDEX IDX_10AA618BBF854D0A (quest_target), PRIMARY KEY(quest_source, quest_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quest_step (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, step_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dofus_quest ADD CONSTRAINT FK_C5C213EF4368DD8C FOREIGN KEY (dofus_id) REFERENCES dofus (id)');
        $this->addSql('ALTER TABLE dofus_quest ADD CONSTRAINT FK_C5C213EF209E9EF4 FOREIGN KEY (quest_id) REFERENCES quest (id)');
        $this->addSql('ALTER TABLE quest ADD CONSTRAINT FK_4317F8171EBBD054 FOREIGN KEY (steps_id) REFERENCES quest_step (id)');
        $this->addSql('ALTER TABLE quest_quest ADD CONSTRAINT FK_10AA618BA6601D85 FOREIGN KEY (quest_source) REFERENCES quest (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quest_quest ADD CONSTRAINT FK_10AA618BBF854D0A FOREIGN KEY (quest_target) REFERENCES quest (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dofus_quest DROP FOREIGN KEY FK_C5C213EF4368DD8C');
        $this->addSql('ALTER TABLE dofus_quest DROP FOREIGN KEY FK_C5C213EF209E9EF4');
        $this->addSql('ALTER TABLE quest DROP FOREIGN KEY FK_4317F8171EBBD054');
        $this->addSql('ALTER TABLE quest_quest DROP FOREIGN KEY FK_10AA618BA6601D85');
        $this->addSql('ALTER TABLE quest_quest DROP FOREIGN KEY FK_10AA618BBF854D0A');
        $this->addSql('DROP TABLE dofus');
        $this->addSql('DROP TABLE dofus_quest');
        $this->addSql('DROP TABLE quest');
        $this->addSql('DROP TABLE quest_quest');
        $this->addSql('DROP TABLE quest_step');
    }
}
