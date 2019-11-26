<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180126081134 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution_approves DROP FOREIGN KEY FK_13A68E8C4235D463');
        $this->addSql('ALTER TABLE contribution_approves DROP FOREIGN KEY FK_13A68E8C4402854A');
        $this->addSql('ALTER TABLE contribution_approves DROP FOREIGN KEY FK_13A68E8CF92F3E70');
        $this->addSql('DROP INDEX IDX_13A68E8C4402854A ON contribution_approves');
        $this->addSql('DROP INDEX IDX_13A68E8CF92F3E70 ON contribution_approves');
        $this->addSql('DROP INDEX IDX_13A68E8C4235D463 ON contribution_approves');
        $this->addSql('ALTER TABLE contribution_approves DROP indicator_id, DROP country_id, DROP technology_id');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contribution_approves ADD indicator_id INT NOT NULL, ADD country_id INT NOT NULL, ADD technology_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8C4235D463 FOREIGN KEY (technology_id) REFERENCES technologies (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8C4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE contribution_approves ADD CONSTRAINT FK_13A68E8CF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('CREATE INDEX IDX_13A68E8C4402854A ON contribution_approves (indicator_id)');
        $this->addSql('CREATE INDEX IDX_13A68E8CF92F3E70 ON contribution_approves (country_id)');
        $this->addSql('CREATE INDEX IDX_13A68E8C4235D463 ON contribution_approves (technology_id)');
    }
}
