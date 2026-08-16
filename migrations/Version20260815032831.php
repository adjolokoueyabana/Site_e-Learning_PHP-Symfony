<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260815032831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create customer order and order item tables for course and lesson purchases';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE customer_order (
                id INT AUTO_INCREMENT NOT NULL,
                status VARCHAR(20) NOT NULL,
                total_amount NUMERIC(10, 2) NOT NULL,
                stripe_checkout_session_id VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                created_by INT DEFAULT NULL,
                updated_by INT DEFAULT NULL,
                user_id INT NOT NULL,
                UNIQUE INDEX UNIQ_3B1CE6A35A18FBC7 (stripe_checkout_session_id),
                INDEX IDX_3B1CE6A3A76ED395 (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4'
        );

        $this->addSql(
            'CREATE TABLE order_item (
                id INT AUTO_INCREMENT NOT NULL,
                item_type VARCHAR(20) NOT NULL,
                title_snapshot VARCHAR(255) NOT NULL,
                unit_price NUMERIC(10, 2) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                created_by INT DEFAULT NULL,
                updated_by INT DEFAULT NULL,
                customer_order_id INT NOT NULL,
                course_id INT DEFAULT NULL,
                lesson_id INT DEFAULT NULL,
                INDEX IDX_52EA1F09A15A2E17 (customer_order_id),
                INDEX IDX_52EA1F09591CC992 (course_id),
                INDEX IDX_52EA1F09CDF80196 (lesson_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4'
        );

        $this->addSql(
            'ALTER TABLE customer_order
            ADD CONSTRAINT FK_3B1CE6A3A76ED395
            FOREIGN KEY (user_id)
            REFERENCES `user` (id)'
        );

        $this->addSql(
            'ALTER TABLE order_item
            ADD CONSTRAINT FK_52EA1F09A15A2E17
            FOREIGN KEY (customer_order_id)
            REFERENCES customer_order (id)'
        );

        $this->addSql(
            'ALTER TABLE order_item
            ADD CONSTRAINT FK_52EA1F09591CC992
            FOREIGN KEY (course_id)
            REFERENCES course (id)
            ON DELETE SET NULL'
        );

        $this->addSql(
            'ALTER TABLE order_item
            ADD CONSTRAINT FK_52EA1F09CDF80196
            FOREIGN KEY (lesson_id)
            REFERENCES lesson (id)
            ON DELETE SET NULL'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE customer_order
            DROP FOREIGN KEY FK_3B1CE6A3A76ED395'
        );

        $this->addSql(
            'ALTER TABLE order_item
            DROP FOREIGN KEY FK_52EA1F09A15A2E17'
        );

        $this->addSql(
            'ALTER TABLE order_item
            DROP FOREIGN KEY FK_52EA1F09591CC992'
        );

        $this->addSql(
            'ALTER TABLE order_item
            DROP FOREIGN KEY FK_52EA1F09CDF80196'
        );

        $this->addSql('DROP TABLE customer_order');
        $this->addSql('DROP TABLE order_item');
    }
}