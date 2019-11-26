<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171228125812 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contribution_requests (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, deadline DATETIME NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_97A7050798260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contributions (id INT AUTO_INCREMENT NOT NULL, contribution_country_request_id INT NOT NULL, user_id INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_76391EFEE9AA1843 (contribution_country_request_id), INDEX IDX_76391EFEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contribution_country_requests (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, contribution_request_id INT NOT NULL, status SMALLINT NOT NULL, UNIQUE INDEX UNIQ_E66DEA96F92F3E70 (country_id), INDEX IDX_E66DEA96B1452D3B (contribution_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contribution_requests ADD CONSTRAINT FK_97A7050798260155 FOREIGN KEY (region_id) REFERENCES regions (id)');
        $this->addSql('ALTER TABLE contributions ADD CONSTRAINT FK_76391EFEE9AA1843 FOREIGN KEY (contribution_country_request_id) REFERENCES contribution_country_requests (id)');
        $this->addSql('ALTER TABLE contributions ADD CONSTRAINT FK_76391EFEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE contribution_country_requests ADD CONSTRAINT FK_E66DEA96F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE contribution_country_requests ADD CONSTRAINT FK_E66DEA96B1452D3B FOREIGN KEY (contribution_request_id) REFERENCES contribution_requests (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution_country_requests DROP FOREIGN KEY FK_E66DEA96B1452D3B');
        $this->addSql('ALTER TABLE contributions DROP FOREIGN KEY FK_76391EFEE9AA1843');
        $this->addSql('DROP TABLE contribution_requests');
        $this->addSql('DROP TABLE contributions');
        $this->addSql('DROP TABLE contribution_country_requests');
    }
}
