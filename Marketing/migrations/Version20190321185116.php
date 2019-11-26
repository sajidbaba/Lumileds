<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190321185116 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution_cell_modifications DROP FOREIGN KEY FK_9FB1D031CB39D93A');
        $this->addSql('ALTER TABLE contribution_cell_modifications ADD CONSTRAINT FK_9FB1D031CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution_cell_modifications DROP FOREIGN KEY FK_9FB1D031CB39D93A');
        $this->addSql('ALTER TABLE contribution_cell_modifications ADD CONSTRAINT FK_9FB1D031CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id)');
    }
}
