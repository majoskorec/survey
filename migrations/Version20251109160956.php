<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109160956 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'add incident entity';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE incident (id INT AUTO_INCREMENT NOT NULL, occurred_at DATE NOT NULL, description LONGTEXT NOT NULL, incident_type VARCHAR(255) NOT NULL, participant_id INT NOT NULL, INDEX IDX_3D03A11A9D1C3019 (participant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11A9D1C3019');
        $this->addSql('DROP TABLE incident');
    }
}
