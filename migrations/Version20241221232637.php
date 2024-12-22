<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221232637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
          $this->addSql("
    CREATE OR REPLACE FUNCTION public.matchs_sans_arbitre_par_saison(p_saison_id integer)
        RETURNS TABLE(match_id integer, match_date date, equipe1_id integer, equipe2_id integer)
        LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN QUERY
        SELECT m.id, m.date, m.equipe1_id, m.equipe2_id
        FROM matchs m
        LEFT JOIN match_arbitre ma ON m.id = ma.match_id
        JOIN journee j ON m.journee_id = j.id
        WHERE ma.arbitre_id IS NULL
        AND j.saison_id = p_saison_id;
    END;
    $$;
    ");

    $this->addSql("
    CREATE OR REPLACE FUNCTION public.calculer_classement_buteurs_saison(p_saison_id integer)
    RETURNS TABLE(joueur_id integer, joueur_nom character varying, total_buts integer)
    LANGUAGE plpgsql
    AS $$
    BEGIN
        RETURN QUERY
        SELECT 
        j.id AS joueur_id,
        j.nom, 
        SUM(s.but)::INTEGER AS total_buts
    FROM 
        joueur j
    JOIN 
        selection s ON j.id = s.joueur_id
    JOIN 
        matchs m ON s.match_id = m.id
    JOIN 
        journee jo ON m.journee_id = jo.id
    WHERE 
        jo.saison_id = p_saison_id
    GROUP BY 
        j.id, j.nom
    ORDER BY 
        total_buts DESC;
    END;
    $$
    ");

    $this->addSql("
CREATE OR REPLACE FUNCTION public.statistiques_joueur_match(p_joueur_id integer, p_match_id integer)
 RETURNS TABLE(joueur_nom character varying, joueur_prenom character varying, joueur_age integer, buts bigint, passes bigint, carton_jaune bigint, carton_rouge bigint, journee_numero integer, date_match date, saison_debut date, saison_fin date)
 LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT 
        j.nom AS joueur_nom,
        j.prenom AS joueur_prenom,
        DATE_PART('year', AGE(CURRENT_DATE, j.date_naissance))::INTEGER AS joueur_age,
        COALESCE(SUM(s.but), 0) AS buts,
        COALESCE(SUM(s.passe), 0) AS passes,
        COALESCE(SUM(s.carton_jaune), 0) AS carton_jaune,
        COALESCE(SUM(s.carton_rouge), 0) AS carton_rouge,
        jn.numero AS journee_numero,  
        m.date AS date_match,         
        s_saison.debut AS saison_debut, 
        s_saison.fin AS saison_fin   
    FROM 
        joueur j
    LEFT JOIN selection s ON j.id = s.joueur_id
    LEFT JOIN matchs m ON s.match_id = m.id
    LEFT JOIN journee jn ON m.journee_id = jn.id
    LEFT JOIN saison s_saison ON jn.saison_id = s_saison.id
    WHERE 
        j.id = p_joueur_id
        AND m.id = p_match_id
    GROUP BY 
        j.nom, j.prenom, j.date_naissance, jn.numero, m.date, s_saison.debut, s_saison.fin;  
END;
$$
    ");

    $this->addSql("
    CREATE OR REPLACE FUNCTION public.statistiques_equipe_match(p_match_id integer, p_equipe_id integer)
 RETURNS TABLE(equipe_nom character varying, total_buts integer, total_passes integer, total_carton_jaune integer, total_carton_rouge integer, saison_debut date, saison_fin date)
 LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT 
        e.nom AS equipe_nom,
        COALESCE(SUM(s.but)::INTEGER, 0) AS total_buts,
        COALESCE(SUM(s.passe)::INTEGER, 0) AS total_passes,
        COALESCE(SUM(s.carton_jaune)::INTEGER, 0) AS total_carton_jaune,
        COALESCE(SUM(s.carton_rouge)::INTEGER, 0) AS total_carton_rouge,
        s_saison.debut AS saison_debut,      
        s_saison.fin AS saison_fin           
    FROM 
        equipe e
    JOIN selection s ON s.equipe_id = e.id
    LEFT JOIN joueur j ON j.id = s.joueur_id
    LEFT JOIN matchs m ON s.match_id = m.id
    LEFT JOIN journee jn ON m.journee_id = jn.id
    LEFT JOIN saison s_saison ON jn.saison_id = s_saison.id
    WHERE 
        e.id = p_equipe_id
        AND s.match_id = p_match_id
    GROUP BY 
        e.nom, s_saison.debut, s_saison.fin;
END;
$$
    ");

    $this->addSql("
    CREATE OR REPLACE FUNCTION verifier_arbitre_disponibilite()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM matchs m
        JOIN match_arbitre ma ON m.id = ma.match_id
        WHERE ma.arbitre_id = NEW.arbitre_id
          AND m.date = (SELECT date FROM matchs WHERE id = NEW.match_id)
          AND ma.match_id != NEW.match_id
    ) THEN
        RAISE EXCEPTION 'Cet arbitre est déjà assigné à un autre match ce jour-là.';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
    ");


    $this->addSql("
CREATE TRIGGER trigger_verifier_arbitre_disponibilite
BEFORE INSERT OR UPDATE ON match_arbitre
FOR EACH ROW
EXECUTE FUNCTION verifier_arbitre_disponibilite();
    ");

        $this->addSql("
CREATE OR REPLACE FUNCTION verifier_arbitre_unique_par_match()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM match_arbitre
        WHERE match_id = NEW.match_id
    ) THEN
        RAISE EXCEPTION 'Un arbitre est déjà assigné à ce match. Veuillez choisir un match différent.';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
    ");

        $this->addSql(" 
CREATE TRIGGER trigger_verifier_arbitre_unique_par_match
BEFORE INSERT ON match_arbitre
FOR EACH ROW
EXECUTE FUNCTION verifier_arbitre_unique_par_match();
    ");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
    }
}
