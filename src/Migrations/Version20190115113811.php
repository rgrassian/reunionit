<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190115113811 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE unavailability_user (unavailability_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_96C9E437F6922FEF (unavailability_id), INDEX IDX_96C9E437A76ED395 (user_id), PRIMARY KEY(unavailability_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE unavailability_user ADD CONSTRAINT FK_96C9E437F6922FEF FOREIGN KEY (unavailability_id) REFERENCES unavailability (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unavailability_user ADD CONSTRAINT FK_96C9E437A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unavailability DROP guests');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE unavailability_user');
        $this->addSql('ALTER TABLE unavailability ADD guests LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\'');
    }
}
