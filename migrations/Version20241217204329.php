<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217204329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la fonction pour classer les buteurs et retourner la meilleure équipe d\'une saison';
    }

    public function up(Schema $schema): void
    {
        // /d nom_table


    $this->addSql("
    CREATE OR REPLACE FUNCTION meilleure_equipe_par_saison(input_saison_id INT)
    RETURNS TABLE (
        equipe_id INT,
        nom_equipe VARCHAR,
        points BIGINT
    ) AS $$
    BEGIN
        RETURN QUERY
        SELECT 
            e.id AS equipe_id, 
            e.nom AS nom_equipe,
            SUM(
                CASE 
                    WHEN (m.equipe1_id = e.id AND m.score_equipe1 > m.score_equipe2) 
                    OR (m.equipe2_id = e.id AND m.score_equipe2 > m.score_equipe1) THEN 3
                    WHEN m.score_equipe1 = m.score_equipe2 THEN 1
                    ELSE 0
                END
            ) AS points
        FROM matchs m
        JOIN journee j ON j.id = m.journee_id
        JOIN equipe e ON e.id = m.equipe1_id OR e.id = m.equipe2_id
        WHERE j.saison_id = input_saison_id
        GROUP BY e.id, e.nom
        ORDER BY points DESC
        LIMIT 1;
    END;
    $$ LANGUAGE plpgsql;
    ");

   //trigger qui verifie si le joeur a deja pas été selectionne pour un match pour une même journée
    $this->addSql("
    CREATE OR REPLACE FUNCTION verifier_joueur_non_selectionne()
    RETURNS TRIGGER AS $$
    BEGIN
        IF EXISTS (
            SELECT 1
            FROM selection s
            WHERE s.match_id = NEW.match_id
            AND s.joueur_id = NEW.joueur_id
        ) THEN
            RAISE EXCEPTION 'Le joueur avec l''id % a déjà été sélectionné pour ce match', NEW.joueur_id;
        END IF;

        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;

    ");
    $this->addSql("

    CREATE TRIGGER trigger_verifier_joueur_non_selectionne
    BEFORE INSERT OR UPDATE ON selection
    FOR EACH ROW
    EXECUTE FUNCTION verifier_joueur_non_selectionne();
    ");

    //trigger qui verifie si le nombre de joueur selectionne pour un match est inferieur a 18
    //C'est parce que dans le cas ou on a 18 joueur dans le match_id1 mais que je fais un update sur le match_id2 forcément ça me fera 19 joueur (le joueur du match_id 2 devient joueur dans le match_id1)
    $this->addSql("
    CREATE OR REPLACE FUNCTION verifier_nombre_joueurs_selectionnes()
    RETURNS TRIGGER AS $$
    DECLARE
     nombre_joueurs INT;
    BEGIN
    SELECT COUNT(*)
    INTO nombre_joueurs
    FROM selection
    WHERE match_id = NEW.match_id;

    IF nombre_joueurs >= 18 THEN
        RAISE EXCEPTION 'Le nombre de joueurs sélectionnés pour ce match est déjà de 18 ou plus';
    ELSIF nombre_joueurs < 18 THEN
        RAISE NOTICE 'Le nombre de joueurs sélectionnés pour ce match est désormais de %', nombre_joueurs + 1;
    END IF;

    RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;
    ");
    $this->addSql("

    CREATE TRIGGER trigger_verifier_nombre_joueurs_selectionnes
    AFTER INSERT OR UPDATE ON selection
    FOR EACH ROW
    EXECUTE FUNCTION verifier_nombre_joueurs_selectionnes();
    ");
    
     // fonction qui retourne les statistiques d'une equipe pour une saison
    $this->addSql("
    CREATE OR REPLACE FUNCTION statistiques_equipe_saison(p_saison_id INTEGER, p_equipe_id INTEGER)
    RETURNS TABLE (
    equipe_id INT,
    nom_equipe VARCHAR,
    nbr_matchs_joues BIGINT,
    nbr_victoires BIGINT,
    nbr_defaites BIGINT,
    nbr_nuls BIGINT,
    nbr_buts_marques BIGINT,
    nbr_buts_encaisses BIGINT,
    nbr_carton_jaune NUMERIC,
    nbr_carton_rouge NUMERIC,
    total_points BIGINT
) 
AS $$
BEGIN
    RETURN QUERY
    SELECT * 
    FROM classement_par_saison(p_saison_id) AS classement
    WHERE classement.equipe_id = p_equipe_id;
END;
$$ LANGUAGE plpgsql;
");
    
}


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP FUNCTION meilleure_equipe_par_saison(INT)');
        $this->addSql('DROP FUNCTION verifier_joueur_non_selectionne()');
        $this->addSql('DROP FUNCTION verifier_nombre_joueurs_selectionnes()');
        $this->addSql('DROP FUNCTION statistiques_equipe_saison(INTEGER, INTEGER)');
        

    }
}