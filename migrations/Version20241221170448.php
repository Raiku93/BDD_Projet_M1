<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221170448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            -- Fonction qui prend l'ID d'un arbitre et renvoie tous les matchs où cet arbitre a officié
            CREATE OR REPLACE FUNCTION matchs_officie_par_arbitre(id_arbitre INT)
            -- on précise les éléments qu'on renvoit vu que c'est plusieurs valeurs, on renvoie une table
            RETURNS TABLE (
                match_id INT,
                journee_id INT,
                equipe1_id INT,
                equipe2_id INT,
                score_equipe1 INT,
                score_equipe2 INT,
                date_match DATE
            ) AS $$
            BEGIN
            -- on retourne la requête qu'on effectue
                RETURN QUERY
                SELECT m.*
                FROM matchs m
                INNER JOIN match_arbitre ma ON m.id = ma.match_id
                WHERE ma.arbitre_id = id_arbitre;
            END;
            $$ LANGUAGE plpgsql;
        ");

        $this->addSql("
            CREATE OR REPLACE FUNCTION classement_par_saison(input_saison_id INT)
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
            ) AS $$
            BEGIN
        RETURN QUERY
        
        SELECT 
            e.id AS equipe_id, 
            e.nom AS nom_equipe,
            SUM(
            CASE 
                WHEN (m.equipe1_id = e.id AND m.score_equipe1 > m.score_equipe2) 
                OR (m.equipe2_id = e.id AND m.score_equipe2 > m.score_equipe1) THEN 1
                ELSE 0 
            END
            ) +
            SUM(
            CASE 
                WHEN (m.equipe1_id = e.id AND m.score_equipe1 < m.score_equipe2) 
                OR (m.equipe2_id = e.id AND m.score_equipe2 < m.score_equipe1) THEN 1
                ELSE 0 
            END
            ) +
            SUM(
            CASE 
                WHEN (m.equipe1_id = e.id OR m.equipe2_id = e.id) 
                    AND m.score_equipe1 = m.score_equipe2 THEN 1
                ELSE 0
            END
            ) AS nbr_matchs_joues,
            SUM(
                CASE 
                    WHEN (m.equipe1_id = e.id AND m.score_equipe1 > m.score_equipe2) 
                    OR (m.equipe2_id = e.id AND m.score_equipe2 > m.score_equipe1) THEN 1
                    ELSE 0 
                END
            ) AS nbr_victoires,
            SUM(
                CASE 
                    WHEN (m.equipe1_id = e.id AND m.score_equipe1 < m.score_equipe2) 
                    OR (m.equipe2_id = e.id AND m.score_equipe2 < m.score_equipe1) THEN 1
                    ELSE 0 
                END
            ) AS nbr_defaites,
            SUM(
                CASE 
                    WHEN (m.equipe1_id = e.id OR m.equipe2_id = e.id) 
                    AND m.score_equipe1 = m.score_equipe2 THEN 1
                    ELSE 0
                END
            ) AS nbr_nuls,
            SUM(
                CASE 
                    WHEN m.equipe1_id = e.id THEN m.score_equipe1
                    ELSE m.score_equipe2
                END
            ) AS nbr_buts_marques,
            SUM(
                CASE 
                    WHEN m.equipe1_id = e.id THEN m.score_equipe2
                    ELSE m.score_equipe1
                END
            ) AS nbr_buts_encaisses,
                    -- on utilise les fonctions d'agrégations juste pour éviter a les mettres dans le group by 
            -- les mettres dans la clause group by est un problème car ça signifierait qu'on doivent se baser sur c valeurs
            -- le problème c'est quelle change pour chaque match on evite ainsi les duplications a noter qu'il est
            -- interdit d'effectuer un group by sur un élément étant une fonction d'agrégation (c pour cette raison)
            SUM(s.nbr_carton_jaune) AS nbr_carton_jaune,
            SUM(s.nbr_carton_rouge) AS nbr_carton_rouge,
            SUM(
                CASE 
                    WHEN (m.equipe1_id = e.id AND m.score_equipe1 > m.score_equipe2) 
                    OR (m.equipe2_id = e.id AND m.score_equipe2 > m.score_equipe1) THEN 3
                    WHEN m.score_equipe1 = m.score_equipe2 THEN 1
                    ELSE 0
                END
            ) AS total_points
        
        FROM matchs m
        JOIN journee j ON j.id = m.journee_id
        JOIN (
            SELECT id, nom FROM equipe
        ) e ON e.id = m.equipe1_id OR e.id = m.equipe2_id
        LEFT JOIN (
            SELECT match_id, 
                COALESCE(SUM(carton_jaune), 0) AS nbr_carton_jaune, 
                COALESCE(SUM(carton_rouge), 0) AS nbr_carton_rouge
            FROM selection
            GROUP BY match_id
        ) s ON m.id = s.match_id
        WHERE j.saison_id = input_saison_id
        GROUP BY e.id, e.nom
        -- critère de classement : points si les points sont les mêmes, buts_marques, etc..
        ORDER BY total_points DESC, nbr_buts_marques DESC, nbr_buts_encaisses ASC;

    END;
    $$ LANGUAGE plpgsql;
        ");

        $this->addSql("
        CREATE OR REPLACE FUNCTION check_equipes_differentes()
        RETURNS TRIGGER AS $$
        BEGIN
            IF NEW.equipe1_id = NEW.equipe2_id THEN
                RAISE EXCEPTION 'Les deux équipes sont identiques !';
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
        ");
        $this->addSql("
        CREATE TRIGGER check_equipes_differentes_match
        BEFORE INSERT OR UPDATE ON matchs
        FOR EACH ROW
        EXECUTE FUNCTION check_equipes_differentes();
        ");

        $this->addSql("
        CREATE OR REPLACE FUNCTION verifier_joueur_disponible()
        RETURNS TRIGGER AS $$
        DECLARE
            match_date DATE;
        BEGIN
            SELECT date INTO match_date
            FROM matchs
            WHERE id = NEW.match_id;

            IF EXISTS (
                SELECT 1
                FROM indisponibilite i
                WHERE i.joueur_id = NEW.joueur_id
                -- on vérifie que la date du match est compris entre le début et la fin ou le début et une fin a null (indisponible jusqu'a x)
                AND (
                    (i.fin IS NULL AND match_date >= i.debut) 
                    OR (match_date BETWEEN i.debut AND i.fin)
                )
            ) THEN
                RAISE EXCEPTION 'Le joueur % est indisponible pour ce match', NEW.joueur_id;
            END IF;

            RETURN NEW; 
        END;
        $$ LANGUAGE plpgsql;
         ");
        $this->addSql("
        CREATE TRIGGER trigger_verifier_joueur_disponible
        BEFORE INSERT OR UPDATE ON selection
        FOR EACH ROW
        EXECUTE FUNCTION verifier_joueur_disponible();
        ");


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
    }
}
