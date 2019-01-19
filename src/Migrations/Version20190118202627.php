<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190118202627 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room CHANGE capacity capacity INT UNSIGNED NOT NULL, CHANGE picture picture VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(80) NOT NULL');
        $this->addSql('ALTER TABLE unavailability CHANGE object object VARCHAR(80) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room CHANGE capacity capacity INT NOT NULL, CHANGE picture picture VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE unavailability CHANGE object object VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
