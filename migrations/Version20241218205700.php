<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for adding the "fin" column to the "inscription" table and creating a trigger to enforce constraints.
 */
final class Version20241218205700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crees le trigger qui assure un joueur soit inscrit au max 3 dans les équipes dans une saison.';
    }

    public function up(Schema $schema): void
    {


        $this->addSql(<<<SQL
CREATE OR REPLACE FUNCTION check_joueur_inscription() RETURNS TRIGGER AS $$
BEGIN
    -- Check if the player is already in a team at the same time
    IF EXISTS (
        SELECT 1
        FROM inscription i
        WHERE i.joueur_id = NEW.joueur_id
          AND i.saison_id = NEW.saison_id
          AND (NEW.fin IS NULL OR NEW.fin > i.date)
          AND (i.fin IS NULL OR i.fin > NEW.date)
    ) THEN
        RAISE EXCEPTION 'Un joueur peut appartenir à une seule équipe à un moment donné dans une saison';
    END IF;

    -- Check if the player is already registered in more than 3 teams in the same season
    IF (SELECT COUNT(*) FROM inscription i WHERE i.joueur_id = NEW.joueur_id AND i.saison_id = NEW.saison_id) >= 3 THEN
        RAISE EXCEPTION 'Un joueur ne peut pas être inscrit dans plus de 3 équipes dans une saison';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

        $this->addSql(<<<SQL
CREATE TRIGGER before_insert_inscription
BEFORE INSERT ON inscription
FOR EACH ROW
EXECUTE FUNCTION check_joueur_inscription();
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TRIGGER IF EXISTS before_insert_inscription ON inscription');
        $this->addSql('DROP FUNCTION IF EXISTS check_joueur_inscription');
    }
}
