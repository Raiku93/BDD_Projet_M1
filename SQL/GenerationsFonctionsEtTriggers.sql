
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
$$;

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
$$;

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
$$;

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

CREATE TRIGGER trigger_verifier_arbitre_disponibilite
BEFORE INSERT OR UPDATE ON match_arbitre
FOR EACH ROW
EXECUTE FUNCTION verifier_arbitre_disponibilite();

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

CREATE TRIGGER trigger_verifier_arbitre_unique_par_match
BEFORE INSERT ON match_arbitre
FOR EACH ROW
EXECUTE FUNCTION verifier_arbitre_unique_par_match();

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

CREATE TRIGGER before_journee_insert
BEFORE INSERT ON journee
FOR EACH ROW
EXECUTE FUNCTION check_max_journees();

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

CREATE TRIGGER trigger_check_max_arbitres
BEFORE INSERT ON saison_arbitre
FOR EACH ROW
EXECUTE FUNCTION check_max_arbitres();

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
$$ LANGUAGE plpgsql;

CREATE TRIGGER before_insert_inscription
BEFORE INSERT ON inscription
FOR EACH ROW
EXECUTE FUNCTION check_joueur_inscription();

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
    CREATE TRIGGER trigger_verifier_joueur_non_selectionne
    BEFORE INSERT OR UPDATE ON selection
FOR EACH ROW
EXECUTE FUNCTION verifier_joueur_non_selectionne();

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
CREATE TRIGGER trigger_verifier_nombre_joueurs_selectionnes
AFTER INSERT OR UPDATE ON selection
FOR EACH ROW
EXECUTE FUNCTION verifier_nombre_joueurs_selectionnes();

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

        CREATE OR REPLACE FUNCTION check_equipes_differentes()
        RETURNS TRIGGER AS $$
        BEGIN
            IF NEW.equipe1_id = NEW.equipe2_id THEN
                RAISE EXCEPTION 'Les deux équipes sont identiques !';
            END IF;
            RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER check_equipes_differentes_match
BEFORE INSERT OR UPDATE ON matchs
FOR EACH ROW
EXECUTE FUNCTION check_equipes_differentes();

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
CREATE TRIGGER trigger_verifier_joueur_disponible
BEFORE INSERT OR UPDATE ON selection
FOR EACH ROW
EXECUTE FUNCTION verifier_joueur_disponible();
