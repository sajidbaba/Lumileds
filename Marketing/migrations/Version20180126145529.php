<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180126145529 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contribution_approve_rows (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, contribution_country_request_id INT NOT NULL, indicator_id INT NOT NULL, country_id INT NOT NULL, segment_id INT NOT NULL, technology_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_3E7D531CA76ED395 (user_id), INDEX IDX_3E7D531CE9AA1843 (contribution_country_request_id), INDEX IDX_3E7D531C4402854A (indicator_id), INDEX IDX_3E7D531CF92F3E70 (country_id), INDEX IDX_3E7D531CDB296AAD (segment_id), INDEX IDX_3E7D531C4235D463 (technology_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531CE9AA1843 FOREIGN KEY (contribution_country_request_id) REFERENCES contribution_country_requests (id)');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531C4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531CF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531CDB296AAD FOREIGN KEY (segment_id) REFERENCES segments (id)');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531C4235D463 FOREIGN KEY (technology_id) REFERENCES technologies (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE contribution_approve_rows');
    }
}
