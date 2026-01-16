<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop self-referencing category_id column from category table
 */
final class Version20260113FixCategory extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop self-referencing category_id column from category';
    }

    public function up(Schema $schema): void
    {
        // Explicitly drop the foreign key first
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C112469DE2');
        // Then drop the column
        $this->addSql('ALTER TABLE category DROP COLUMN category_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1397DA4B8B FOREIGN KEY (category_id) REFERENCES category (id)');
    }
}
