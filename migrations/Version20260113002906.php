<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260113002906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Fixed: only add created_at, attachment already exists, is_like handled separately
        if (!$schema->getTable('user')->hasColumn('created_at')) {
            $this->addSql('ALTER TABLE `user` ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\'');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE is_like');
        $this->addSql('ALTER TABLE article DROP attachment');
        $this->addSql('ALTER TABLE user DROP created_at');
    }
}
