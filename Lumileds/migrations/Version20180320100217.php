<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180320100217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contribution_indicator_requests (id INT AUTO_INCREMENT NOT NULL, contribution_country_request_id INT NOT NULL, status SMALLINT NOT NULL, indicator_group SMALLINT NOT NULL, INDEX IDX_F3B4A239E9AA1843 (contribution_country_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contribution_indicator_requests ADD CONSTRAINT FK_F3B4A239E9AA1843 FOREIGN KEY (contribution_country_request_id) REFERENCES contribution_country_requests (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE contribution_indicator_requests');
    }
}
