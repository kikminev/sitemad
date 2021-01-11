<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210108212314 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE album (id INT NOT NULL, site_id INT NOT NULL, user_customer_id INT NOT NULL, default_image_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, sequence_order INT DEFAULT NULL, is_active BOOLEAN DEFAULT NULL, is_deleted BOOLEAN DEFAULT NULL, translated_title JSON DEFAULT NULL, translated_content JSON DEFAULT NULL, translated_keywords JSON DEFAULT NULL, translated_meta_description JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_39986E43F6BD1646 ON album (site_id)');
        $this->addSql('CREATE INDEX IDX_39986E433A8E0A66 ON album (user_customer_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39986E433BE11523 ON album (default_image_id)');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E433A8E0A66 FOREIGN KEY (user_customer_id) REFERENCES user_customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E433BE11523 FOREIGN KEY (default_image_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE album');
    }
}