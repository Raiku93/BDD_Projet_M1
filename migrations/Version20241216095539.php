<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216095539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription_officiel (id SERIAL NOT NULL, officiel_id INT DEFAULT NULL, equipe_id INT DEFAULT NULL, saison_id INT DEFAULT NULL, nb_officiel INT NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_558E88ACB80840D5 ON inscription_officiel (officiel_id)');
        $this->addSql('CREATE INDEX IDX_558E88AC6D861B89 ON inscription_officiel (equipe_id)');
        $this->addSql('CREATE INDEX IDX_558E88ACF965414C ON inscription_officiel (saison_id)');
        $this->addSql('ALTER TABLE inscription_officiel ADD CONSTRAINT FK_558E88ACB80840D5 FOREIGN KEY (officiel_id) REFERENCES officiel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inscription_officiel ADD CONSTRAINT FK_558E88AC6D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inscription_officiel ADD CONSTRAINT FK_558E88ACF965414C FOREIGN KEY (saison_id) REFERENCES saison (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matchs ADD status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE inscription_officiel DROP CONSTRAINT FK_558E88ACB80840D5');
        $this->addSql('ALTER TABLE inscription_officiel DROP CONSTRAINT FK_558E88AC6D861B89');
        $this->addSql('ALTER TABLE inscription_officiel DROP CONSTRAINT FK_558E88ACF965414C');
        $this->addSql('DROP TABLE inscription_officiel');
        $this->addSql('ALTER TABLE matchs DROP status');
    }
}
