<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop is_like table - use `like` table instead
 */
final class Version20260113DropIsLike extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop is_like table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS is_like');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE is_like (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }
}
