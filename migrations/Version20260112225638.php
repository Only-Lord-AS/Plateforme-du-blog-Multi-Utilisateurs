<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112225638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE is_like (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `like` ADD article_id INT NOT NULL, ADD user_id INT NOT NULL, ADD islike TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B37294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC6340B37294869C ON `like` (article_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B3A76ED395 ON `like` (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE is_like');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B37294869C');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3A76ED395');
        $this->addSql('DROP INDEX IDX_AC6340B37294869C ON `like`');
        $this->addSql('DROP INDEX IDX_AC6340B3A76ED395 ON `like`');
        $this->addSql('ALTER TABLE `like` DROP article_id, DROP user_id, DROP islike');
    }
}
