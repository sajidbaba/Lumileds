<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180313090610 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cell_versions DROP FOREIGN KEY FK_4CBC1A174BBC2705');
        $this->addSql('ALTER TABLE cell_versions ADD CONSTRAINT FK_4CBC1A174BBC2705 FOREIGN KEY (version_id) REFERENCES versions (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cell_versions DROP FOREIGN KEY FK_4CBC1A174BBC2705');
        $this->addSql('ALTER TABLE cell_versions ADD CONSTRAINT FK_4CBC1A174BBC2705 FOREIGN KEY (version_id) REFERENCES versions (id)');
    }
}
