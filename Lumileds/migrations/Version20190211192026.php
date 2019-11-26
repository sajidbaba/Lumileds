<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190211192026 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD84402854A');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD84402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4402854A');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution_approve_rows DROP FOREIGN KEY FK_3E7D531C4402854A');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531C4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD8836088D7');
        $this->addSql('DROP INDEX UNIQ_55C1CBD8836088D7 ON cells');
        $this->addSql('ALTER TABLE cells DROP error_id');
        $this->addSql('ALTER TABLE cell_errors DROP FOREIGN KEY FK_F0622245CB39D93A');
        $this->addSql('ALTER TABLE cell_errors ADD CONSTRAINT FK_F0622245CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD84402854A');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD84402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE contribution_approve_rows DROP FOREIGN KEY FK_3E7D531C4402854A');
        $this->addSql('ALTER TABLE contribution_approve_rows ADD CONSTRAINT FK_3E7D531C4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4402854A');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE cell_errors DROP FOREIGN KEY FK_F0622245CB39D93A');
        $this->addSql('ALTER TABLE cell_errors ADD CONSTRAINT FK_F0622245CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id)');
        $this->addSql('ALTER TABLE cells ADD error_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD8836088D7 FOREIGN KEY (error_id) REFERENCES cell_errors (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55C1CBD8836088D7 ON cells (error_id)');
    }
}
