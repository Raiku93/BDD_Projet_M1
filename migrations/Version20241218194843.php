<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218194843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'un trigger pour vérifier le nombre maximum d\'arbitres par saison dans la table saison_arbitre.';
    }

    public function up(Schema $schema): void
    {
        // Création de la fonction trigger
        $this->addSql(<<<SQL
            CREATE OR REPLACE FUNCTION check_max_arbitres()
            RETURNS TRIGGER AS $$
            DECLARE
                max_arbitres INT;         -- Nombre maximum d'arbitres pour la saison
                current_arbitres INT;     -- Nombre actuel d'arbitres assignés à la saison
            BEGIN
                -- Vérifier si l'ID de la saison est valide
                IF NEW.saison_id IS NULL THEN
                    RAISE EXCEPTION 'L''ID de la saison ne peut pas être NULL.';
                END IF;

                -- Vérifier si l'ID de l'arbitre est valide
                IF NEW.arbitre_id IS NULL THEN
                    RAISE EXCEPTION 'L''ID de l''arbitre ne peut pas être NULL.';
                END IF;

                -- Récupérer le nombre maximum d'arbitres pour la saison
                SELECT s.nb_arbitre
                INTO max_arbitres
                FROM saison s
                WHERE s.id = NEW.saison_id;

                -- Si la saison n'existe pas ou le nombre maximum d'arbitres est NULL
                IF max_arbitres IS NULL THEN
                    RAISE EXCEPTION 'Saison non trouvée ou le nombre maximum d''arbitres (nb_arbitre) n''est pas défini pour la saison %', NEW.saison_id;
                END IF;

                -- Compter le nombre d'arbitres déjà affectés à cette saison
                SELECT COUNT(*)
                INTO current_arbitres
                FROM saison_arbitre sa
                WHERE sa.saison_id = NEW.saison_id;

                -- Vérifier si le nombre d'arbitres dépasse le maximum
                IF current_arbitres + 1 > max_arbitres THEN
                    RAISE EXCEPTION 'Nombre maximum d''arbitres atteint pour la saison %', NEW.saison_id;
                END IF;

                -- Si tout est bon, autoriser l'insertion
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // Création du trigger
        $this->addSql(<<<SQL
            CREATE TRIGGER trigger_check_max_arbitres
            BEFORE INSERT ON saison_arbitre
            FOR EACH ROW
            EXECUTE FUNCTION check_max_arbitres();
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Suppression du trigger et de la fonction
        $this->addSql('DROP TRIGGER IF EXISTS trigger_check_max_arbitres ON saison_arbitre');
        $this->addSql('DROP FUNCTION IF EXISTS check_max_arbitres');
    }
}
