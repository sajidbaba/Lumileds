<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190211192748 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DELETE FROM `indicators` WHERE id IN (21, 22, 23, 24, 25, 27, 28, 29, 30, 31)');
    }

    public function down(Schema $schema)
    {

    }
}
