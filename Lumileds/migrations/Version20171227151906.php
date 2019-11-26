<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171227151906 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE countries DROP FOREIGN KEY FK_5D66EBAD98260155');
        $this->addSql('ALTER TABLE countries ADD CONSTRAINT FK_5D66EBAD98260155 FOREIGN KEY (region_id) REFERENCES regions (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE countries DROP FOREIGN KEY FK_5D66EBAD98260155');
        $this->addSql('ALTER TABLE countries ADD CONSTRAINT FK_5D66EBAD98260155 FOREIGN KEY (region_id) REFERENCES regions (id)');
    }
}
