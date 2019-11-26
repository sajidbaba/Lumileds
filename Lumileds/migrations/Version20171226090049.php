<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171226090049 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cell_versions (id INT AUTO_INCREMENT NOT NULL, version_id INT DEFAULT NULL, cell_id INT DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, INDEX IDX_4CBC1A174BBC2705 (version_id), INDEX IDX_4CBC1A17CB39D93A (cell_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE versions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(255) NOT NULL, approved_at DATETIME DEFAULT NULL, approved_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cell_versions ADD CONSTRAINT FK_4CBC1A174BBC2705 FOREIGN KEY (version_id) REFERENCES versions (id)');
        $this->addSql('ALTER TABLE cell_versions ADD CONSTRAINT FK_4CBC1A17CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id)');
        $this->addSql('ALTER TABLE cells ADD error_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD8836088D7 FOREIGN KEY (error_id) REFERENCES cell_errors (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55C1CBD8836088D7 ON cells (error_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cell_versions DROP FOREIGN KEY FK_4CBC1A174BBC2705');
        $this->addSql('DROP TABLE cell_versions');
        $this->addSql('DROP TABLE versions');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD8836088D7');
        $this->addSql('DROP INDEX UNIQ_55C1CBD8836088D7 ON cells');
        $this->addSql('ALTER TABLE cells DROP error_id');
    }
}
