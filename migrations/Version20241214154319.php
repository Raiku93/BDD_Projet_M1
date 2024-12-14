<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241214154319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE arbitre (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE equipe (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE indisponibilite (id SERIAL NOT NULL, joueur_id INT DEFAULT NULL, debut DATE NOT NULL, fin DATE NOT NULL, raison VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8717036FA9E2D76C ON indisponibilite (joueur_id)');
        $this->addSql('CREATE TABLE inscription (id SERIAL NOT NULL, joueur_id INT DEFAULT NULL, equipe_id INT DEFAULT NULL, saison_id INT DEFAULT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5E90F6D6A9E2D76C ON inscription (joueur_id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D66D861B89 ON inscription (equipe_id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D6F965414C ON inscription (saison_id)');
        $this->addSql('CREATE TABLE joueur (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, post VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE journee (id SERIAL NOT NULL, saison_id INT DEFAULT NULL, numero INT NOT NULL, debut DATE NOT NULL, fin DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DC179AEDF965414C ON journee (saison_id)');
        $this->addSql('CREATE TABLE league (id SERIAL NOT NULL, display_name VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE match_arbitre (id SERIAL NOT NULL, match_id INT DEFAULT NULL, arbitre_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5714B9012ABEACD6 ON match_arbitre (match_id)');
        $this->addSql('CREATE INDEX IDX_5714B901943A5F0 ON match_arbitre (arbitre_id)');
        $this->addSql('CREATE TABLE matchs (id SERIAL NOT NULL, journee_id INT DEFAULT NULL, equipe1_id INT DEFAULT NULL, equipe2_id INT DEFAULT NULL, score_equipe1 INT NOT NULL, score_equipe2 INT NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6B1E6041CF066148 ON matchs (journee_id)');
        $this->addSql('CREATE INDEX IDX_6B1E60414265900C ON matchs (equipe1_id)');
        $this->addSql('CREATE INDEX IDX_6B1E604150D03FE2 ON matchs (equipe2_id)');
        $this->addSql('CREATE TABLE officiel (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE saison (id SERIAL NOT NULL, league_id INT DEFAULT NULL, debut DATE NOT NULL, fin DATE NOT NULL, nb_equipe INT NOT NULL, nb_arbitre INT NOT NULL, nb_remplacement INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C0D0D58658AFC4DE ON saison (league_id)');
        $this->addSql('CREATE TABLE saison_arbitre (id SERIAL NOT NULL, arbitre_id INT DEFAULT NULL, saison_id INT DEFAULT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C37E5358943A5F0 ON saison_arbitre (arbitre_id)');
        $this->addSql('CREATE INDEX IDX_C37E5358F965414C ON saison_arbitre (saison_id)');
        $this->addSql('CREATE TABLE selection (id SERIAL NOT NULL, match_id INT DEFAULT NULL, joueur_id INT DEFAULT NULL, equipe_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, but INT NOT NULL, passe INT NOT NULL, carton_jaune INT NOT NULL, carton_rouge INT NOT NULL, post VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_96A50CD72ABEACD6 ON selection (match_id)');
        $this->addSql('CREATE INDEX IDX_96A50CD7A9E2D76C ON selection (joueur_id)');
        $this->addSql('CREATE INDEX IDX_96A50CD76D861B89 ON selection (equipe_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE indisponibilite ADD CONSTRAINT FK_8717036FA9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6A9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D66D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6F965414C FOREIGN KEY (saison_id) REFERENCES saison (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE journee ADD CONSTRAINT FK_DC179AEDF965414C FOREIGN KEY (saison_id) REFERENCES saison (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE match_arbitre ADD CONSTRAINT FK_5714B9012ABEACD6 FOREIGN KEY (match_id) REFERENCES matchs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE match_arbitre ADD CONSTRAINT FK_5714B901943A5F0 FOREIGN KEY (arbitre_id) REFERENCES arbitre (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E6041CF066148 FOREIGN KEY (journee_id) REFERENCES journee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E60414265900C FOREIGN KEY (equipe1_id) REFERENCES equipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE matchs ADD CONSTRAINT FK_6B1E604150D03FE2 FOREIGN KEY (equipe2_id) REFERENCES equipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE saison ADD CONSTRAINT FK_C0D0D58658AFC4DE FOREIGN KEY (league_id) REFERENCES league (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE saison_arbitre ADD CONSTRAINT FK_C37E5358943A5F0 FOREIGN KEY (arbitre_id) REFERENCES arbitre (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE saison_arbitre ADD CONSTRAINT FK_C37E5358F965414C FOREIGN KEY (saison_id) REFERENCES saison (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE selection ADD CONSTRAINT FK_96A50CD72ABEACD6 FOREIGN KEY (match_id) REFERENCES matchs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE selection ADD CONSTRAINT FK_96A50CD7A9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE selection ADD CONSTRAINT FK_96A50CD76D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE indisponibilite DROP CONSTRAINT FK_8717036FA9E2D76C');
        $this->addSql('ALTER TABLE inscription DROP CONSTRAINT FK_5E90F6D6A9E2D76C');
        $this->addSql('ALTER TABLE inscription DROP CONSTRAINT FK_5E90F6D66D861B89');
        $this->addSql('ALTER TABLE inscription DROP CONSTRAINT FK_5E90F6D6F965414C');
        $this->addSql('ALTER TABLE journee DROP CONSTRAINT FK_DC179AEDF965414C');
        $this->addSql('ALTER TABLE match_arbitre DROP CONSTRAINT FK_5714B9012ABEACD6');
        $this->addSql('ALTER TABLE match_arbitre DROP CONSTRAINT FK_5714B901943A5F0');
        $this->addSql('ALTER TABLE matchs DROP CONSTRAINT FK_6B1E6041CF066148');
        $this->addSql('ALTER TABLE matchs DROP CONSTRAINT FK_6B1E60414265900C');
        $this->addSql('ALTER TABLE matchs DROP CONSTRAINT FK_6B1E604150D03FE2');
        $this->addSql('ALTER TABLE saison DROP CONSTRAINT FK_C0D0D58658AFC4DE');
        $this->addSql('ALTER TABLE saison_arbitre DROP CONSTRAINT FK_C37E5358943A5F0');
        $this->addSql('ALTER TABLE saison_arbitre DROP CONSTRAINT FK_C37E5358F965414C');
        $this->addSql('ALTER TABLE selection DROP CONSTRAINT FK_96A50CD72ABEACD6');
        $this->addSql('ALTER TABLE selection DROP CONSTRAINT FK_96A50CD7A9E2D76C');
        $this->addSql('ALTER TABLE selection DROP CONSTRAINT FK_96A50CD76D861B89');
        $this->addSql('DROP TABLE arbitre');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE indisponibilite');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP TABLE joueur');
        $this->addSql('DROP TABLE journee');
        $this->addSql('DROP TABLE league');
        $this->addSql('DROP TABLE match_arbitre');
        $this->addSql('DROP TABLE matchs');
        $this->addSql('DROP TABLE officiel');
        $this->addSql('DROP TABLE saison');
        $this->addSql('DROP TABLE saison_arbitre');
        $this->addSql('DROP TABLE selection');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
