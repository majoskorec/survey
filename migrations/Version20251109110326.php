<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109110326 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'suggestion';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE suggestion (id INT AUTO_INCREMENT NOT NULL, suggestion LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:date_point)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE suggestion');
    }
}
