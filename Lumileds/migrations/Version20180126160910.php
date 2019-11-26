<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180126160910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD8836088D7');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD8836088D7 FOREIGN KEY (error_id) REFERENCES cell_errors (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cell_versions DROP FOREIGN KEY FK_4CBC1A17CB39D93A');
        $this->addSql('ALTER TABLE cell_versions ADD CONSTRAINT FK_4CBC1A17CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cell_versions DROP FOREIGN KEY FK_4CBC1A17CB39D93A');
        $this->addSql('ALTER TABLE cell_versions ADD CONSTRAINT FK_4CBC1A17CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id)');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD8836088D7');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD8836088D7 FOREIGN KEY (error_id) REFERENCES cell_errors (id)');
    }
}
