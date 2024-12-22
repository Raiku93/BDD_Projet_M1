<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218192504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute un trigger pour vérifier le nombre maximum de journées dans une saison.';
    }

    public function up(Schema $schema): void
    {
        // Création de la fonction de trigger
        $this->addSql(<<<SQL
    CREATE OR REPLACE FUNCTION check_max_journees()
    RETURNS TRIGGER AS $$
    DECLARE
        max_journees INT;
        current_journees INT;
    BEGIN
        -- Calcul du nombre maximum de journées pour la saison
        SELECT 2 * (s.nb_equipe - 1)  -- Correction du nom de la colonne
        INTO max_journees
        FROM saison s
        WHERE s.id = NEW.saison_id;

        -- Vérifie si le nombre maximum est défini correctement
        IF max_journees IS NULL THEN
            RAISE EXCEPTION 'Saison non trouvée ou nbEquipe non défini pour la saison %', NEW.saison_id;
        END IF;

        -- Compte les journées déjà existantes pour la saison
        SELECT COUNT(*)
        INTO current_journees
        FROM journee j
        WHERE j.saison_id = NEW.saison_id;  -- Correction de l'attribut

        -- Vérification de la contrainte
        IF current_journees + 1 > max_journees THEN
            RAISE EXCEPTION 'Nombre maximum de journées atteint pour la saison %', NEW.saison_id;
        END IF;

        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;
SQL);


        // Création du trigger
        $this->addSql(<<<SQL
            CREATE TRIGGER before_journee_insert
            BEFORE INSERT ON journee
            FOR EACH ROW
            EXECUTE FUNCTION check_max_journees();
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Suppression du trigger et de la fonction
        $this->addSql('DROP TRIGGER IF EXISTS before_journee_insert ON journee');
        $this->addSql('DROP FUNCTION IF EXISTS check_max_journees');
    }
}
