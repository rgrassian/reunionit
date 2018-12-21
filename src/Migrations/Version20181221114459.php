<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181221114459 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE occupied (id INT AUTO_INCREMENT NOT NULL, organiser_id INT NOT NULL, room_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, attendants LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', object VARCHAR(255) NOT NULL, type INT NOT NULL, INDEX IDX_178943DDA0631C12 (organiser_id), INDEX IDX_178943DD54177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, capacity INT NOT NULL, name VARCHAR(255) NOT NULL, features LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(64) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE occupied ADD CONSTRAINT FK_178943DDA0631C12 FOREIGN KEY (organiser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE occupied ADD CONSTRAINT FK_178943DD54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE occupied DROP FOREIGN KEY FK_178943DD54177093');
        $this->addSql('ALTER TABLE occupied DROP FOREIGN KEY FK_178943DDA0631C12');
        $this->addSql('DROP TABLE occupied');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE user');
    }
}
