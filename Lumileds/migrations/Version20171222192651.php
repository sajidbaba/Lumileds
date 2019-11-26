<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171222192651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE indicators (id INT NOT NULL, country_id INT DEFAULT NULL, segment_id INT DEFAULT NULL, technology_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type INT NOT NULL, INDEX IDX_49B719A0F92F3E70 (country_id), INDEX IDX_49B719A0DB296AAD (segment_id), INDEX IDX_49B719A04235D463 (technology_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE countries (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5D66EBAD5E237E06 (name), INDEX IDX_5D66EBAD98260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_1483A5E9C05FB297 (confirmation_token), INDEX IDX_1483A5E9FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_country (user_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_B7ED76CA76ED395 (user_id), INDEX IDX_B7ED76CF92F3E70 (country_id), PRIMARY KEY(user_id, country_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, indicator_id INT DEFAULT NULL, technology_id INT DEFAULT NULL, priority INT NOT NULL, INDEX IDX_E52FFDEE4402854A (indicator_id), INDEX IDX_E52FFDEE4235D463 (technology_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE technologies (id INT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4CCBFB185E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE segments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_26CEDB295E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cells (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, segment_id INT DEFAULT NULL, indicator_id INT DEFAULT NULL, technology_id INT DEFAULT NULL, year VARCHAR(4) NOT NULL, value VARCHAR(255) DEFAULT NULL, INDEX IDX_55C1CBD8F92F3E70 (country_id), INDEX IDX_55C1CBD8DB296AAD (segment_id), INDEX IDX_55C1CBD84402854A (indicator_id), INDEX IDX_55C1CBD84235D463 (technology_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE regions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A26779F35E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_F06D39705E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cell_errors (id INT AUTO_INCREMENT NOT NULL, cell_id INT DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_F0622245CB39D93A (cell_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE indicators ADD CONSTRAINT FK_49B719A0F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE indicators ADD CONSTRAINT FK_49B719A0DB296AAD FOREIGN KEY (segment_id) REFERENCES segments (id)');
        $this->addSql('ALTER TABLE indicators ADD CONSTRAINT FK_49B719A04235D463 FOREIGN KEY (technology_id) REFERENCES technologies (id)');
        $this->addSql('ALTER TABLE countries ADD CONSTRAINT FK_5D66EBAD98260155 FOREIGN KEY (region_id) REFERENCES regions (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('ALTER TABLE user_country ADD CONSTRAINT FK_B7ED76CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_country ADD CONSTRAINT FK_B7ED76CF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4235D463 FOREIGN KEY (technology_id) REFERENCES technologies (id)');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD8F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD8DB296AAD FOREIGN KEY (segment_id) REFERENCES segments (id)');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD84402854A FOREIGN KEY (indicator_id) REFERENCES indicators (id)');
        $this->addSql('ALTER TABLE cells ADD CONSTRAINT FK_55C1CBD84235D463 FOREIGN KEY (technology_id) REFERENCES technologies (id)');
        $this->addSql('ALTER TABLE cell_errors ADD CONSTRAINT FK_F0622245CB39D93A FOREIGN KEY (cell_id) REFERENCES cells (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4402854A');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD84402854A');
        $this->addSql('ALTER TABLE indicators DROP FOREIGN KEY FK_49B719A0F92F3E70');
        $this->addSql('ALTER TABLE user_country DROP FOREIGN KEY FK_B7ED76CF92F3E70');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD8F92F3E70');
        $this->addSql('ALTER TABLE user_country DROP FOREIGN KEY FK_B7ED76CA76ED395');
        $this->addSql('ALTER TABLE indicators DROP FOREIGN KEY FK_49B719A04235D463');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4235D463');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD84235D463');
        $this->addSql('ALTER TABLE indicators DROP FOREIGN KEY FK_49B719A0DB296AAD');
        $this->addSql('ALTER TABLE cells DROP FOREIGN KEY FK_55C1CBD8DB296AAD');
        $this->addSql('ALTER TABLE cell_errors DROP FOREIGN KEY FK_F0622245CB39D93A');
        $this->addSql('ALTER TABLE countries DROP FOREIGN KEY FK_5D66EBAD98260155');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9FE54D947');
        $this->addSql('DROP TABLE indicators');
        $this->addSql('DROP TABLE countries');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_country');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE technologies');
        $this->addSql('DROP TABLE segments');
        $this->addSql('DROP TABLE cells');
        $this->addSql('DROP TABLE regions');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE cell_errors');
    }
}
