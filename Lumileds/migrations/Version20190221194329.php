<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190221194329 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DELETE FROM `contribution_indicator_requests` WHERE indicator_group IN (2, 3)');
    }

    public function down(Schema $schema)
    {

    }
}
