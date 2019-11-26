<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190525143340 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('UPDATE `indicators` SET `type` = 3 WHERE id = 12');
    }

    public function down(Schema $schema)
    {
        $this->addSql('UPDATE `indicators` SET `type` = 1 WHERE id = 12');
    }
}
