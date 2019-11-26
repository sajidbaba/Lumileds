<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180119150107 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contribution_cell_modifications (id INT AUTO_INCREMENT NOT NULL, contribution_id INT NOT NULL, user_id INT NOT NULL, cell_id INT NOT NULL, value VARCHAR(255) DEFAULT NULL, INDEX IDX_9FB1D031FE5E5FBD (contribution_id), INDEX IDX_9FB1D031A76ED395 (user_id), INDEX IDX_9FB1D031CB39D93A (cell_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contribution_approves (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, contribution_country_request_id INT NOT NULL, indicator_id INT NOT NULL, country_id INT NOT NULL, segment_id INT NOT NULL, technology_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_13A68E8CA76ED395 (user_id), INDEX IDX_13A68E8CE9AA1843 (contribution_country_request_id), INDEX IDX_13A68E8C4402854A (indicator_id), INDEX IDX_13A68E8CF92F3E70 (country_id), INDEX IDX_13A68E8CDB296AAD (segment_id), INDEX IDX_13A68E8C4235D463 (technology_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contribution_cell_modifications ADD CONSTRAINT FK_9FB1D031FE5E5FBD FOREIGN KEY (contribution_id) REFERENCES contributions (id)');
        $this->addSql('ALTER TABLE contribution_cell_modifications ADD CONSTRAINT FK_9FB1D031A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contribution_cell_modifications ADD CONSTRAINT FK_9FB1D031CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8CE9AA1843 FOREIGN KEY (contribution_country_request_id) REFERENCES contribution_country_requests (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8C4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8CF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8CDB296AAD FOREIGN KEY (segment_id) REFERENCES segments (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8C4235D463 FOREIGN KEY (technology_id) REFERENCES technologies (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE contribution_cell_modifications');
        $this->addSql('DROP TABLE contribution_approves');
    }
}
