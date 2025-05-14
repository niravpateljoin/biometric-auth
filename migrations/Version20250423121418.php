<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423121418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE bio_metric_data (id INT AUTO_INCREMENT NOT NULL, data LONGBLOB NOT NULL, credential_id VARCHAR(255) NOT NULL, created_time DATETIME(6) NOT NULL, last_used_time DATETIME(6) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_31825D72A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role ENUM('ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER') DEFAULT 'ROLE_USER' NOT NULL, enabled TINYINT(1) NOT NULL, enable2fa TINYINT(1) NOT NULL, enable_bio_metrics_for2fa TINYINT(1) NOT NULL, created_at DATETIME(6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bio_metric_data ADD CONSTRAINT FK_31825D72A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE bio_metric_data DROP FOREIGN KEY FK_31825D72A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bio_metric_data
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
    }
}
