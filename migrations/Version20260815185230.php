<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260815185230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certification (id INT AUTO_INCREMENT NOT NULL, certificate_number VARCHAR(50) NOT NULL, issued_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, user_id INT NOT NULL, theme_id INT NOT NULL, UNIQUE INDEX UNIQ_6C3C6D753005EFE3 (certificate_number), INDEX IDX_6C3C6D75A76ED395 (user_id), INDEX IDX_6C3C6D7559027487 (theme_id), UNIQUE INDEX UNIQ_USER_THEME_CERTIFICATION (user_id, theme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D7559027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75A76ED395');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D7559027487');
        $this->addSql('DROP TABLE certification');
    }
}
