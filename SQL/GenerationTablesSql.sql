

CREATE TABLE arbitre (
    id integer NOT NULL PRIMARY KEY,
    nom character varying(255) NOT NULL
);

CREATE TABLE league (
    id integer NOT NULL PRIMARY KEY,
    display_name character varying(255) NOT NULL,
    country character varying(255) NOT NULL,
    date_creation date NOT NULL
);

CREATE TABLE joueur (
    id integer NOT NULL PRIMARY KEY,
    nom character varying(255) NOT NULL,
    prenom character varying(255) NOT NULL,
    date_naissance date NOT NULL,
    post character varying(255) NOT NULL
);

CREATE TABLE equipe (
    id integer NOT NULL PRIMARY KEY,
    nom character varying(255) NOT NULL,
    ville character varying(255) NOT NULL,
    date_creation date NOT NULL
);

CREATE TABLE saison (
    id integer NOT NULL PRIMARY KEY,
    league_id integer REFERENCES league (id),
    debut date NOT NULL,
    fin date NOT NULL,
    nb_equipe integer NOT NULL,
    nb_arbitre integer NOT NULL,
    nb_remplacement integer NOT NULL
);

CREATE TABLE indisponibilite (
    id integer NOT NULL PRIMARY KEY,
    joueur_id integer REFERENCES joueur (id),
    debut date NOT NULL,
    -- peut-être enlever le not null
    fin date,
    raison character varying(255) NOT NULL
);

CREATE TABLE journee (
    id integer NOT NULL PRIMARY KEY,
    saison_id integer REFERENCES saison (id),
    numero integer NOT NULL,
    debut date NOT NULL,
    fin date NOT NULL
);

CREATE TABLE matchs (
    id integer NOT NULL PRIMARY KEY,
    journee_id integer REFERENCES journee (id),
    equipe1_id integer REFERENCES equipe (id),
    equipe2_id integer REFERENCES equipe (id),
    score_equipe1 integer NOT NULL,
    score_equipe2 integer NOT NULL,
    date date NOT NULL,
    status character varying(255) NOT NULL
);

CREATE TABLE inscription (
    id integer NOT NULL PRIMARY KEY,
    joueur_id integer REFERENCES joueur (id),
    equipe_id integer REFERENCES equipe (id),
    saison_id integer REFERENCES saison (id),
    date date NOT NULL,
    date_fin date
);

CREATE TABLE officiel (
    id integer NOT NULL PRIMARY KEY,
    nom character varying(255) NOT NULL,
    prenom character varying(255) NOT NULL,
    role character varying(255) NOT NULL
);
CREATE TABLE inscription_officiel (
    id integer NOT NULL PRIMARY KEY,
    officiel_id integer REFERENCES officiel (id),
    equipe_id integer REFERENCES equipe (id),
    saison_id integer REFERENCES saison (id),
    nb_officiel integer NOT NULL,
    date date NOT NULL
);

CREATE TABLE match_arbitre (
    id integer NOT NULL PRIMARY KEY,
    match_id integer REFERENCES matchs (id),
    arbitre_id integer REFERENCES arbitre (id)
);

CREATE TABLE saison_arbitre (
    id integer NOT NULL PRIMARY KEY,
    arbitre_id integer REFERENCES arbitre (id),
    saison_id integer REFERENCES saison (id),
    date date NOT NULL
);

CREATE TABLE selection (
    id integer NOT NULL PRIMARY KEY,
    match_id integer REFERENCES matchs (id),
    joueur_id integer REFERENCES joueur (id),
    equipe_id integer REFERENCES equipe (id),
    type character varying(255) NOT NULL,
    but integer NOT NULL,
    passe integer NOT NULL,
    carton_jaune integer NOT NULL,
    carton_rouge integer NOT NULL,
    post character varying(255) NOT NULL
);


