<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190102111030 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE unavailability (id INT AUTO_INCREMENT NOT NULL, organiser_id INT NOT NULL, room_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, guests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', object VARCHAR(255) NOT NULL, type INT NOT NULL, INDEX IDX_F0016D1A0631C12 (organiser_id), INDEX IDX_F0016D154177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D1A0631C12 FOREIGN KEY (organiser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D154177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('DROP TABLE occupied');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE occupied (id INT AUTO_INCREMENT NOT NULL, organiser_id INT NOT NULL, room_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, attendants LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', object VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, type INT NOT NULL, INDEX IDX_178943DD54177093 (room_id), INDEX IDX_178943DDA0631C12 (organiser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE occupied ADD CONSTRAINT FK_178943DD54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE occupied ADD CONSTRAINT FK_178943DDA0631C12 FOREIGN KEY (organiser_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE unavailability');
    }
}
