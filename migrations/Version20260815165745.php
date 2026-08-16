<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260815165745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lesson progress table with unique user and lesson progression';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE lesson_progress (
                id INT AUTO_INCREMENT NOT NULL,
                completed TINYINT NOT NULL,
                completed_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                created_by INT DEFAULT NULL,
                updated_by INT DEFAULT NULL,
                user_id INT NOT NULL,
                lesson_id INT NOT NULL,
                INDEX IDX_6A46B85FA76ED395 (user_id),
                INDEX IDX_6A46B85FCDF80196 (lesson_id),
                UNIQUE INDEX UNIQ_USER_LESSON_PROGRESS (user_id, lesson_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4'
        );

        $this->addSql(
            'ALTER TABLE lesson_progress
            ADD CONSTRAINT FK_6A46B85FA76ED395
            FOREIGN KEY (user_id)
            REFERENCES `user` (id)
            ON DELETE CASCADE'
        );

        $this->addSql(
            'ALTER TABLE lesson_progress
            ADD CONSTRAINT FK_6A46B85FCDF80196
            FOREIGN KEY (lesson_id)
            REFERENCES lesson (id)
            ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE lesson_progress
            DROP FOREIGN KEY FK_6A46B85FA76ED395'
        );

        $this->addSql(
            'ALTER TABLE lesson_progress
            DROP FOREIGN KEY FK_6A46B85FCDF80196'
        );

        $this->addSql(
            'DROP TABLE lesson_progress'
        );
    }
}