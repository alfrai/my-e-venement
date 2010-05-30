--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: billeterie; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA billeterie;


--
-- Name: pro; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA pro;


--
-- Name: sco; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA sco;


--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: -
--

CREATE PROCEDURAL LANGUAGE plpgsql;


SET search_path = billeterie, pg_catalog;

--
-- Name: resume_tickets; Type: TYPE; Schema: billeterie; Owner: -
--

CREATE TYPE resume_tickets AS (
	transaction bigint,
	manifid integer,
	nb bigint,
	tarif character varying,
	reduc integer,
	printed boolean,
	canceled boolean,
	prix numeric,
	prixspec numeric
);


SET search_path = public, pg_catalog;

--
-- Name: resume_tickets; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE resume_tickets AS (
	transaction bigint,
	manifid integer,
	nb bigint,
	tarif character varying,
	reduc integer,
	printed boolean,
	canceled boolean,
	prix numeric,
	prixspec numeric
);


SET search_path = billeterie, pg_catalog;

--
-- Name: addpreresa(bigint, bigint, integer, integer, boolean, character varying, integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION addpreresa(bigint, bigint, integer, integer, boolean, character varying, integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
DECLARE
account ALIAS FOR $1;
transac ALIAS FOR $2;
manif ALIAS FOR $3;
reduction ALIAS FOR $4;
annulation ALIAS FOR $5;
tarif ALIAS FOR $6;
nbloops ALIAS FOR $7;
nb integer;
tarif_id integer;

BEGIN

nb := 0;

tarif_id := get_tarifid(manif,tarif);

WHILE nb < ABS(nbloops) LOOP
  nb := nb + 1;
  INSERT INTO reservation_pre ("accountid","manifid","tarifid","reduc","transaction","annul")
  VALUES ( account, manif,tarif_id,reduction,transac,annulation );
END LOOP;

RETURN nb > 0;
END;$_$;


--
-- Name: contingeanting(bigint, bigint, bigint, bigint); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION contingeanting(bigint, bigint, bigint, bigint) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$BEGIN
PERFORM * FROM contingeant WHERE transaction = $1;
IF ( FOUND )
THEN RETURN false;
ELSE INSERT INTO contingeant (transaction,accountid,personneid,fctorgid) VALUES ($1,$2,$3,$4);
     RETURN true;
END IF;
END;$_$;


--
-- Name: FUNCTION contingeanting(bigint, bigint, bigint, bigint); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION contingeanting(bigint, bigint, bigint, bigint) IS 'fonction permettant d''ajouter _au_besoin_ une entrée dans la table contingeant.
retourne true si aucun enregistrement n''existait avant l''appel à la fonction (qui en a alors rajouté un),
retourne false sinon.
$1: transaction
$2: accountid
$3: personneid
$4: fctorgid';


--
-- Name: counttickets(bigint, boolean); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION counttickets(bigint, boolean) RETURNS bigint
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT count(*) AS RESULT
FROM reservation_cur AS resa
WHERE resa.canceled = false
AND resa_preid = $1;$_$;


--
-- Name: FUNCTION counttickets(bigint, boolean); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION counttickets(bigint, boolean) IS 'Utilisé lors de l''impression de billets';


--
-- Name: decontingeanting(bigint, integer, bigint, integer, integer, integer, integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION decontingeanting(bigint, integer, bigint, integer, integer, integer, integer) RETURNS boolean
    LANGUAGE plpgsql STRICT
    AS $_$DECLARE
trans ALIAS FOR $1;
manif ALIAS FOR $2;
account ALIAS FOR $3;
oldtarif ALIAS FOR $4;
newtarif ALIAS FOR $5;
reduction ALIAS FOR $6;
qty ALIAS FOR $7;

i INTEGER := 0;
selled INTEGER := 0;
mass RECORD;
BEGIN

-- calcul du nombre de places vendues
selled := (SELECT nb FROM masstickets WHERE tarifid = newtarif AND manifid = manif AND transaction = trans) - qty;

-- Si on a rien vendu, on ne met rien à jour
IF ( selled <= 0 ) THEN RETURN true; END IF;

-- Mise à jour de la table masstickets (on doit avoir qqch à mettre à jour)
UPDATE masstickets SET nb = qty WHERE tarifid = newtarif AND manifid = manif AND transaction = trans;
IF ( NOT FOUND ) THEN RETURN false; END IF;

LOOP
-- condition de sortie de boucle
IF ( i >= selled ) THEN RETURN true; END IF;

-- Si on n'a pas de pré-resa en attente... on en ajoute à la volée
PERFORM * FROM reservation_pre AS resa WHERE transaction = trans AND manifid = manif AND tarifid = oldtarif;
IF ( NOT FOUND )
THEN 
  INSERT INTO reservation_pre (transaction,accountid,manifid,tarifid,reduc) SELECT trans, account, manif, oldtarif, 0;
  IF ( NOT FOUND )
  THEN RETURN false;
  END IF;
END IF;

-- On passe les pré-resa en résa réelle (puisque les tickets ont été vendus)
INSERT INTO reservation_cur (resa_preid,accountid)
VALUES ((SELECT MIN(id) AS resa_preid
         FROM reservation_pre AS resa
         WHERE transaction = trans AND manifid = manif AND account != 0 AND tarifid = oldtarif), account);
IF ( NOT FOUND ) THEN RETURN false; END IF;

-- On met à jour la nature des tarifs (on doit avoir qqch à mettre à jour)
UPDATE reservation_pre
SET tarifid = newtarif, reduc = reduction
WHERE id = (SELECT MIN(id) AS min FROM reservation_pre AS resa WHERE transaction = trans AND tarifid = oldtarif AND manifid = manif);
IF ( NOT FOUND ) THEN RETURN false; END IF;

i := i+1;

END LOOP;
RETURN true;
END;$_$;


--
-- Name: FUNCTION decontingeanting(bigint, integer, bigint, integer, integer, integer, integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION decontingeanting(bigint, integer, bigint, integer, integer, integer, integer) IS 'fonction permettant de mettre à jour les tables reservation_pre et masstickets pour les places contingeantées réellement vendues, ainsi que reservation_cur...
retourne true par défaut, false en cas d''erreur.
$1: transaction
$2: manifid
$3: accountid
$4: old tarifid
$5: new tarifid
$6: reduc
$7: quantity';


--
-- Name: deftva(integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION deftva(integer) RETURNS numeric
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT evtcat.txtva AS RETURN
FROM evenement AS evt, evt_categorie AS evtcat
WHERE evt.id = $1
AND evtcat.id = evt.categorie$_$;


--
-- Name: firstresa(integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION firstresa(integer) RETURNS timestamp with time zone
    LANGUAGE plpgsql STABLE STRICT SECURITY DEFINER
    AS $_$DECLARE
    resa RECORD;
BEGIN

FOR resa IN
    SELECT min(date) FROM reservation_pre WHERE manifid = $1
LOOP
IF resa.min IS NULL
THEN RETURN now();
ELSE RETURN resa.min;
END IF;
END LOOP;
RETURN NULL;
END;$_$;


--
-- Name: FUNCTION firstresa(integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION firstresa(integer) IS 'donne la date de la première reservation effectuée sur une manifestation
$1: manifid';


--
-- Name: firstresa(integer, character varying); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION firstresa(integer, character varying) RETURNS timestamp with time zone
    LANGUAGE plpgsql STABLE STRICT
    AS $_$DECLARE
    resa RECORD;
    mid  ALIAS FOR $1;
    tkey ALIAS FOR $2;
BEGIN

FOR resa IN
    SELECT min(pre.date) FROM reservation_pre AS pre, tarif WHERE pre.manifid = mid AND tarifid = tarif.id AND tarif.key = tkey
LOOP
	IF resa.min IS NULL
	THEN RETURN now();
	ELSE RETURN resa.min;
	END IF;
END LOOP;
RETURN NULL;

END;$_$;


--
-- Name: FUNCTION firstresa(integer, character varying); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION firstresa(integer, character varying) IS 'donne la date de la première reservation d''un tarif donné effectuée sur une manifestation
$1: manifid
$2: tarif.key';


--
-- Name: get_second_if_not_null(numeric, numeric); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION get_second_if_not_null(numeric, numeric) RETURNS numeric
    LANGUAGE plpgsql STABLE
    AS $_$BEGIN

IF ( $2 IS NOT NULL )
THEN RETURN $2;
ELSE RETURN $1;
END IF;

END;$_$;


--
-- Name: FUNCTION get_second_if_not_null(numeric, numeric); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION get_second_if_not_null(numeric, numeric) IS 'Retourne la seconde valeur si elle n''est pas nulle
Retourne la premiere sinon
(pratique avec les prix et prixspec des manifs)';


--
-- Name: get_tarifid(integer, character varying); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION get_tarifid(integer, character varying) RETURNS integer
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT id AS result
FROM tarif_manif
WHERE manifid = $1
  AND key = $2$_$;


--
-- Name: FUNCTION get_tarifid(integer, character varying); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION get_tarifid(integer, character varying) IS 'Donne l''id d''un tarif $2 pour la manifestation $1
$1: manifid
$2: tarif.key';


--
-- Name: get_tarifid_contingeant(integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION get_tarifid_contingeant(integer) RETURNS integer
    LANGUAGE sql STABLE STRICT
    AS $$SELECT id AS result FROM tarif WHERE contingeant ORDER BY date DESC LIMIT 1;$$;


--
-- Name: FUNCTION get_tarifid_contingeant(integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION get_tarifid_contingeant(integer) IS 'Retourne l''id du dernier tarif de places contingeantées entré et valid pour la manif $1 (à travers la vue tarif_manif)';


--
-- Name: getprice(integer, character varying); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION getprice(integer, character varying) RETURNS numeric
    LANGUAGE plpgsql STABLE STRICT
    AS $_$DECLARE
    buf NUMERIC;
BEGIN
    
    buf := (	SELECT prix
    		FROM manifestation_tarifs
    		WHERE manifestationid = $1
    		  AND tarifid = get_tarifid($1,$2));
    IF ( buf IS NOT NULL )
    THEN RETURN buf;
    END IF;
    
    buf := (	SELECT prix
    		FROM tarif
    		WHERE id = get_tarifid($1,$2));
    RETURN buf;
END;$_$;


--
-- Name: FUNCTION getprice(integer, character varying); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION getprice(integer, character varying) IS 'retourne le prix d''un ticket sans réduction pour la manif $1 pour le tarif $2
$1: manif.id
$2: tarif.key';


--
-- Name: getprice(integer, integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION getprice(integer, integer) RETURNS numeric
    LANGUAGE plpgsql STABLE STRICT
    AS $_$DECLARE
    buf NUMERIC;
BEGIN
    
    buf := (SELECT prix FROM manifestation_tarifs WHERE manifestationid = $1 AND tarifid = $2);
    IF ( buf IS NOT NULL )
    THEN RETURN buf;
    END IF;
    
    buf := (SELECT prix FROM tarif WHERE id = $2);
    RETURN buf;
END;$_$;


--
-- Name: FUNCTION getprice(integer, integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION getprice(integer, integer) IS 'retourne le prix d''un ticket sans réduction pour la manif $1 pour le tarif $2
$1: manif.id
$2: tarif.id';


--
-- Name: is_plnum_valid(integer, integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION is_plnum_valid(integer, integer) RETURNS boolean
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT siteid IN (SELECT siteid FROM manifestation WHERE id = $1 AND plnum)
FROM site_plnum
WHERE id = $2;$_$;


--
-- Name: FUNCTION is_plnum_valid(integer, integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION is_plnum_valid(integer, integer) IS 'vérifie que la place $2 réservée est réservable pour la manifestatio
n $1 est valide
$1: manifid
$2: plnum';


--
-- Name: is_tarif_valid(integer, integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION is_tarif_valid(integer, integer) RETURNS boolean
    LANGUAGE sql STABLE STRICT
    AS $_$
SELECT firstresa($1, tarif."key") >= date FROM tarif WHERE id = $2;
$_$;


--
-- Name: FUNCTION is_tarif_valid(integer, integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION is_tarif_valid(integer, integer) IS 'vérifie qu''un tarif d''id $2 pour la manifestation $1 est valide
$1: manifid
$2: tarifid';


--
-- Name: manif_update(); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION manif_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
   BEGIN
      NEW.updated = NOW();
      RETURN NEW;
   END;
  $$;


--
-- Name: onlyonevalidticket(bigint, boolean); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION onlyonevalidticket(bigint, boolean) RETURNS boolean
    LANGUAGE plpgsql STABLE STRICT
    AS $_$DECLARE
ret boolean;
BEGIN

IF $2 = true THEN RETURN true;
ELSE RETURN counttickets($1,$2) <= 0;
END IF;

END;$_$;


--
-- Name: ticket_num(bigint, integer, character varying); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION ticket_num(bigint, integer, character varying) RETURNS bigint
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT zeroifnull(max(ticketid)::bigint)+1 AS RESULT 
FROM reservation_pre, reservation_cur, tarif
WHERE tarif.id = tarifid
  AND resa_preid = reservation_pre.id
  AND tarif.key = $3
  AND reservation_pre.manifid = $1
  AND reservation_pre.reduc = $2
  AND reservation_cur.canceled = false;$_$;


--
-- Name: FUNCTION ticket_num(bigint, integer, character varying); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION ticket_num(bigint, integer, character varying) IS 'retourne le numéro de billet à venir en fonction de :
$1: l''id de la manifestation
$2: la réduction accordée
$3: la clé du tarif choisi';


--
-- Name: toomanyannul(integer, boolean); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION toomanyannul(integer, boolean) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
DECLARE
  manif ALIAS FOR $1;
  annul ALIAS FOR $2;
  result boolean;
BEGIN
result := true;
IF ( annul )
THEN
  SELECT INTO result sum(nb) >= 0
  FROM tickets2print_bymanif(manif)
  WHERE canceled = false
    AND printed = true;
END IF;
RETURN result;
END;$_$;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: manifestation; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE manifestation (
    id integer NOT NULL,
    evtid integer NOT NULL,
    siteid integer,
    date timestamp with time zone NOT NULL,
    duree interval,
    description text,
    jauge integer,
    txtva numeric(5,2) NOT NULL,
    colorid integer,
    plnum boolean DEFAULT false NOT NULL,
    updated timestamp with time zone DEFAULT now() NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE manifestation; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE manifestation IS 'Manifestation d''un évènement (représentation d''un spéctacle par exemple)';


--
-- Name: COLUMN manifestation.evtid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.evtid IS 'evenement.id';


--
-- Name: COLUMN manifestation.siteid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.siteid IS 'site.id';


--
-- Name: COLUMN manifestation.date; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.date IS 'date et heure de la manifestation';


--
-- Name: COLUMN manifestation.duree; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.duree IS 'duree reelle (contrairement a la duree de l''evenement qui est theorique)';


--
-- Name: COLUMN manifestation.jauge; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.jauge IS 'Jauge maximale pour cette manifestation';


--
-- Name: COLUMN manifestation.txtva; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.txtva IS 'taux de tva à appliquer à la manif';


--
-- Name: COLUMN manifestation.colorid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.colorid IS 'color.id';


--
-- Name: COLUMN manifestation.updated; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.updated IS 'date de dernier accès en écriture';


--
-- Name: COLUMN manifestation.created; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation.created IS 'date de création';


--
-- Name: manifestation_tarifs; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE manifestation_tarifs (
    id integer NOT NULL,
    manifestationid integer NOT NULL,
    tarifid integer NOT NULL,
    prix numeric(5,3) NOT NULL
);


--
-- Name: TABLE manifestation_tarifs; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE manifestation_tarifs IS 'Donne des tarifs particuliers pour une manifestation';


--
-- Name: COLUMN manifestation_tarifs.manifestationid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation_tarifs.manifestationid IS 'manifestation.id';


--
-- Name: COLUMN manifestation_tarifs.tarifid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation_tarifs.tarifid IS 'tarif.id';


--
-- Name: COLUMN manifestation_tarifs.prix; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manifestation_tarifs.prix IS 'prix spécifique à une séance pour un tarif donné';


--
-- Name: reservation; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE reservation (
    id bigint NOT NULL,
    accountid bigint NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE reservation; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE reservation IS 'Table servant de patron pour l''ensemble des tables liées aux réservations';


--
-- Name: COLUMN reservation.accountid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation.accountid IS 'public.account.id';


--
-- Name: COLUMN reservation.date; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation.date IS 'date où l''opération a eu lieu';


--
-- Name: reservation_cur; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE reservation_cur (
    id bigint NOT NULL,
    accountid bigint NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    resa_preid bigint NOT NULL,
    canceled boolean DEFAULT false NOT NULL,
    CONSTRAINT reservation_cur_resa_onevalidticket CHECK (onlyonevalidticket(resa_preid, canceled))
);


--
-- Name: TABLE reservation_cur; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE reservation_cur IS 'Réservation à proprement parlé (bon de commande signé et billet édité)';


--
-- Name: COLUMN reservation_cur.accountid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_cur.accountid IS 'account.id';


--
-- Name: COLUMN reservation_cur.canceled; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_cur.canceled IS 'true si le ticket a été annulé';


--
-- Name: reservation_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE reservation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: reservation_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE reservation_id_seq OWNED BY reservation.id;


--
-- Name: reservation_pre; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE reservation_pre (
    manifid integer NOT NULL,
    tarifid integer NOT NULL,
    reduc integer DEFAULT 0,
    transaction bigint NOT NULL,
    annul boolean DEFAULT false NOT NULL,
    plnum integer,
    dematerialized_passed boolean DEFAULT false NOT NULL,
    CONSTRAINT reservation_pre_annul_key CHECK (toomanyannul(manifid, annul)),
    CONSTRAINT reservation_pre_plnum_valid_key CHECK (is_plnum_valid(manifid, plnum)),
    CONSTRAINT resrevation_pre_tarif_valid_key CHECK (is_tarif_valid(manifid, tarifid))
)
INHERITS (reservation);


--
-- Name: TABLE reservation_pre; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE reservation_pre IS 'Pré-réservations (bon de commande édité)';


--
-- Name: COLUMN reservation_pre.tarifid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_pre.tarifid IS 'tarif.id';


--
-- Name: COLUMN reservation_pre.reduc; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_pre.reduc IS 'réduction accordée, en %age (ex: 70 => 70% de réduction)';


--
-- Name: COLUMN reservation_pre.transaction; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_pre.transaction IS 'numero de transaction... permet de repérer une transaction en cours';


--
-- Name: COLUMN reservation_pre.annul; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_pre.annul IS 'si ''true'' alors c''est un billet d''annulation comptant en négatif';


--
-- Name: COLUMN reservation_pre.dematerialized_passed; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN reservation_pre.dematerialized_passed IS 'Indique si l''entrée a déjà été comptabilisée pour ce billet dématérialisé';


--
-- Name: tarif; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE tarif (
    id integer NOT NULL,
    description character varying(255),
    key character varying(5) NOT NULL,
    prix numeric(8,3) NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    desact boolean DEFAULT false NOT NULL,
    contingeant boolean DEFAULT false NOT NULL,
    CONSTRAINT tarif_prix_contingeant_key CHECK (((contingeant AND (prix = (0)::numeric)) OR (NOT contingeant)))
);


--
-- Name: TABLE tarif; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE tarif IS 'Définit les tarifs par défaut...';


--
-- Name: COLUMN tarif.description; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN tarif.description IS 'description du tarif';


--
-- Name: COLUMN tarif.key; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN tarif.key IS 'diminutif du tarif (tp = plein tarif, sc = scolaire, g = groupes, ...)';


--
-- Name: COLUMN tarif.prix; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN tarif.prix IS 'tarif exact dans la monaie courante, avec deux décimaux de précision';


--
-- Name: COLUMN tarif.date; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN tarif.date IS 'Date de création du tarif';


--
-- Name: COLUMN tarif.desact; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN tarif.desact IS 'Le tarif est désactivé : desact = true';


--
-- Name: COLUMN tarif.contingeant; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN tarif.contingeant IS 'si le tarif correspond à une place contingeantée, = true';


--
-- Name: tarif_manif; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW tarif_manif AS
    SELECT tarif.id, tarif.description, tarif.key, tarif.prix, tarif.desact, tarif.contingeant, tarif.date, manif.manifestationid AS manifid, manif.prix AS prixspec FROM tarif, manifestation_tarifs manif WHERE ((tarif.id = manif.tarifid) AND (tarif.date IN (SELECT max(tmp.date) AS max FROM tarif tmp WHERE (((tmp.key)::text = (tarif.key)::text) AND (tmp.date <= firstresa(manif.manifestationid, tarif.key))) GROUP BY tmp.key))) UNION SELECT tarif.id, tarif.description, tarif.key, tarif.prix, tarif.desact, tarif.contingeant, tarif.date, manifestation.id AS manifid, NULL::unknown AS prixspec FROM tarif, manifestation WHERE ((NOT ((tarif.id, manifestation.id) IN (SELECT manifestation_tarifs.tarifid, manifestation_tarifs.manifestationid FROM manifestation_tarifs WHERE ((manifestation_tarifs.manifestationid = manifestation.id) AND (manifestation_tarifs.tarifid = tarif.id))))) AND (tarif.date IN (SELECT max(tmp.date) AS max FROM tarif tmp WHERE (((tmp.key)::text = (tarif.key)::text) AND (tmp.date <= firstresa(manifestation.id, tarif.key))) GROUP BY tmp.key))) ORDER BY 5, 3;


--
-- Name: VIEW tarif_manif; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW tarif_manif IS 'Affiche les tarifs par défaut ainsi que les tarifs particulier pour chaque séance... notez qu''il faut prendre le tarif particulier en compte à la place du tarif par défaut s''il existe.
(fonction très lente dès que le nombre de tarifs et le nombre de manifestations est important)';


--
-- Name: tickets2print; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW tickets2print AS
    (SELECT resa.id, resa.transaction, resa.manifid, resa.id AS resaid, tarif.key AS tarif, resa.reduc, true AS printed, ticket.canceled, resa.annul FROM reservation_pre resa, tarif, reservation_cur ticket WHERE (((resa.id = ticket.resa_preid) AND (tarif.id = resa.tarifid)) AND (NOT ((resa.id, ticket.canceled) IN (SELECT reservation_cur.resa_preid, reservation_cur.canceled FROM reservation_cur WHERE (reservation_cur.canceled = true))))) UNION SELECT resa.id, resa.transaction, resa.manifid, resa.id AS resaid, tarif.key AS tarif, resa.reduc, true AS printed, ticket.canceled, resa.annul FROM reservation_pre resa, tarif, reservation_cur ticket WHERE (((resa.id = ticket.resa_preid) AND (tarif.id = resa.tarifid)) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false)))))) UNION SELECT resa.id, resa.transaction, resa.manifid, resa.id AS resaid, tarif.key AS tarif, resa.reduc, false AS printed, false AS canceled, resa.annul FROM reservation_pre resa, tarif WHERE ((NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.resa_preid = resa.id)))) AND (tarif.id = resa.tarifid)) ORDER BY 2, 3, 5, 6, 7, 8;


--
-- Name: VIEW tickets2print; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW tickets2print IS 'Les tickets et leurs états';


--
-- Name: resumetickets2print; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW resumetickets2print AS
    SELECT tickets2print.transaction, tickets2print.manifid, count(*) AS nb, tickets2print.tarif, tickets2print.reduc, tickets2print.printed, tickets2print.canceled, tarif.prix, tarif.prixspec FROM tickets2print, tarif_manif tarif WHERE (((tickets2print.annul = false) AND ((tarif.key)::text = (tickets2print.tarif)::text)) AND (tarif.manifid = tickets2print.manifid)) GROUP BY tickets2print.transaction, tickets2print.manifid, tickets2print.tarif, tickets2print.reduc, tickets2print.printed, tickets2print.canceled, tickets2print.annul, tarif.prix, tarif.prixspec UNION SELECT tickets2print.transaction, tickets2print.manifid, (- count(*)) AS nb, tickets2print.tarif, tickets2print.reduc, tickets2print.printed, tickets2print.canceled, tarif.prix, tarif.prixspec FROM tickets2print, tarif_manif tarif WHERE (((tickets2print.annul = true) AND ((tarif.key)::text = (tickets2print.tarif)::text)) AND (tarif.manifid = tickets2print.manifid)) GROUP BY tickets2print.transaction, tickets2print.manifid, tickets2print.tarif, tickets2print.reduc, tickets2print.printed, tickets2print.canceled, tickets2print.annul, tarif.prix, tarif.prixspec;


--
-- Name: VIEW resumetickets2print; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW resumetickets2print IS 'regrouppement de tickets à montrer (en fonction des tickets et de leur état)
(vue très lente dès qu''il y a un certain nombre de transactions, billets, ...)';


--
-- Name: tickets2print_bymanif(integer); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION tickets2print_bymanif(integer) RETURNS SETOF resumetickets2print
    LANGUAGE plpgsql STRICT
    AS $_$DECLARE
    tickets resumetickets2print;
BEGIN
    FOR tickets IN

 SELECT tickets2print."transaction", tickets2print.manifid, -(annul::integer*2-1)*count(*) AS nb, tickets2print.tarif, tickets2print.reduc, tickets2print.printed, tickets2print.canceled, tarif.prix, tarif.prixspec
   FROM tickets2print, tarif_manif tarif
  WHERE tickets2print.manifid = $1 AND tarif."key"::text = tickets2print.tarif::text AND tarif.manifid = tickets2print.manifid
  GROUP BY transaction,annul,tickets2print.manifid,tarif,reduc,printed,canceled,prix,prixspec

    LOOP RETURN NEXT tickets; END LOOP;
    RETURN;
END;$_$;


--
-- Name: FUNCTION tickets2print_bymanif(integer); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION tickets2print_bymanif(integer) IS 'Retourne les billets imprimés ou imprimés mais échoués pour la manifestation spécifiée en argument';


--
-- Name: tickets2print_bytransac(bigint); Type: FUNCTION; Schema: billeterie; Owner: -
--

CREATE FUNCTION tickets2print_bytransac(bigint) RETURNS SETOF resumetickets2print
    LANGUAGE plpgsql STRICT
    AS $_$DECLARE
        tickets resume_tickets;
        BEGIN
            FOR tickets IN
            
             SELECT tickets2print."transaction", tickets2print.manifid, -(annul::integer*2-1)*count(*) AS nb, tickets2print.tarif, tickets2print.reduc, tickets2print.printed, tickets2print.canceled
                FROM tickets2print
                  WHERE tickets2print.transaction = $1
                    GROUP BY transaction,annul,manifid,tarif,reduc,printed,canceled
                    
                        LOOP RETURN NEXT tickets; END LOOP;
                            RETURN;
                            END;$_$;


--
-- Name: FUNCTION tickets2print_bytransac(bigint); Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON FUNCTION tickets2print_bytransac(bigint) IS 'Retourne les billets imprimés ou imprimés mais échoués en fonction de leur numéro de transaction spécifié en argument';


SET search_path = pro, pg_catalog;

--
-- Name: get_contingeants(integer); Type: FUNCTION; Schema: pro; Owner: -
--

CREATE FUNCTION get_contingeants(integer) RETURNS bigint
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT -SUM(annul::integer*2-1) AS RESULT
    FROM billeterie.reservation_pre AS pre, billeterie.contingeant AS cont
    WHERE manifid = $1
      AND pre.transaction = cont.transaction
        AND cont.transaction NOT IN ( SELECT transaction FROM billeterie.masstickets )
        AND cont.fctorgid IN (SELECT fctorgid FROM contingentspro)$_$;


--
-- Name: is_auto_paid(integer); Type: FUNCTION; Schema: pro; Owner: -
--

CREATE FUNCTION is_auto_paid(integer) RETURNS boolean
    LANGUAGE sql STABLE STRICT
    AS $_$
	SELECT billeterie.getprice($1,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1)) = 0 AS RESULT;
$_$;


SET search_path = public, pg_catalog;

--
-- Name: get_personneid(integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION get_personneid(integer) RETURNS bigint
    LANGUAGE sql STABLE STRICT
    AS $_$SELECT personneid AS result FROM org_personne WHERE id = $1;$_$;


--
-- Name: FUNCTION get_personneid(integer); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION get_personneid(integer) IS 'retourne l''id d''une personne investie de la fonction $1
$1: org_personne.id';


--
-- Name: zeroifnull(bigint); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION zeroifnull(bigint) RETURNS bigint
    LANGUAGE plpgsql IMMUTABLE
    AS $_$BEGIN
IF $1 IS NULL THEN RETURN 0;
ELSE RETURN $1;
END IF;
END;$_$;


SET search_path = billeterie, pg_catalog;

--
-- Name: preselled; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE preselled (
    id integer NOT NULL,
    transaction bigint NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    accountid bigint
);


--
-- Name: TABLE preselled; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE preselled IS 'table "virtuelle" regroupant les places commandées (bdc) et les places contingeantées (soit en dépot soit bloquées) (contingeant).';


--
-- Name: COLUMN preselled.transaction; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN preselled.transaction IS 'transaction.id';


--
-- Name: COLUMN preselled.accountid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN preselled.accountid IS 'account.id';


--
-- Name: preselled_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE preselled_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: preselled_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE preselled_id_seq OWNED BY preselled.id;


--
-- Name: bdc; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE bdc (
)
INHERITS (preselled);


--
-- Name: TABLE bdc; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE bdc IS 'Enregistrement du bon de commande... signifie que les places sont pré-réservées';


--
-- Name: COLUMN bdc.transaction; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN bdc.transaction IS 'ce sur quoi porte le BdC';


--
-- Name: COLUMN bdc.accountid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN bdc.accountid IS 'account.id';


--
-- Name: color; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE color (
    id integer NOT NULL,
    libelle character varying(127) NOT NULL,
    color character varying(6) NOT NULL
);


--
-- Name: TABLE color; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE color IS 'Permet de donner des couleurs aux manifestations. attention à choisir des couleurs assez claires, proches du blanc.';


--
-- Name: COLUMN color.color; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN color.color IS 'Valeur RGB de type HTML de la couleur correspondant au nom';


--
-- Name: color_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE color_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: color_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE color_id_seq OWNED BY color.id;


--
-- Name: colors; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW colors AS
    SELECT color.id, color.libelle, color.color FROM color UNION SELECT NULL::unknown AS id, NULL::unknown AS libelle, NULL::unknown AS color;


--
-- Name: VIEW colors; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW colors IS 'permet d''avoir des manifestations sans couleur facilement dans la vue info_resa';


--
-- Name: contingeant; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE contingeant (
    personneid bigint NOT NULL,
    fctorgid bigint,
    closed boolean DEFAULT false NOT NULL
)
INHERITS (preselled);


--
-- Name: COLUMN contingeant.personneid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN contingeant.personneid IS 'personne.id';


--
-- Name: COLUMN contingeant.fctorgid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN contingeant.fctorgid IS 'org_personne.id';


--
-- Name: evenement; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE evenement (
    id integer NOT NULL,
    organisme1 integer,
    organisme2 integer,
    organisme3 integer,
    nom character varying(255) NOT NULL,
    description text,
    categorie integer,
    typedesc character varying(255),
    mscene character varying(255),
    mscene_lbl character varying(255),
    textede character varying(255),
    textede_lbl character varying(255),
    duree interval,
    ages numeric(5,2)[],
    code character varying(5),
    creation timestamp with time zone DEFAULT now() NOT NULL,
    modification timestamp with time zone DEFAULT now() NOT NULL,
    metaevt character varying(255),
    petitnom character varying(40),
    tarifweb numeric(8,3),
    extradesc text,
    extraspec text,
    imageurl character varying(255),
    tarifwebgroup numeric(8,3)
);


--
-- Name: TABLE evenement; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE evenement IS 'Titre raccourci pour l''impression des tickets';


--
-- Name: COLUMN evenement.organisme1; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.organisme1 IS '1er organisme createur de l''evenement';


--
-- Name: COLUMN evenement.organisme2; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.organisme2 IS '2nd organisme createur de l''evenement';


--
-- Name: COLUMN evenement.organisme3; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.organisme3 IS '3ème organisme createur de l''evenement';


--
-- Name: COLUMN evenement.nom; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.nom IS 'nom de l''evenement';


--
-- Name: COLUMN evenement.description; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.description IS 'description de l''evenement';


--
-- Name: COLUMN evenement.categorie; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.categorie IS 'evt_categorie.id';


--
-- Name: COLUMN evenement.typedesc; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.typedesc IS 'Description du genre d''evenement';


--
-- Name: COLUMN evenement.mscene; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.mscene IS 'nom du metteur en scene';


--
-- Name: COLUMN evenement.mscene_lbl; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.mscene_lbl IS '"label" de la mise en scene';


--
-- Name: COLUMN evenement.textede; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.textede IS 'nom de l''auteur';


--
-- Name: COLUMN evenement.textede_lbl; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.textede_lbl IS '"label" de l''auteur';


--
-- Name: COLUMN evenement.duree; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.duree IS 'duree theorique d''une manifestation';


--
-- Name: COLUMN evenement.ages; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.ages IS 'ages minimum et maximum dans un tableau (dans cet ordre)';


--
-- Name: COLUMN evenement.code; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.code IS 'code de l''évènement';


--
-- Name: COLUMN evenement.creation; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.creation IS 'date de creation';


--
-- Name: COLUMN evenement.modification; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.modification IS 'date de dernière modification';


--
-- Name: COLUMN evenement.metaevt; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evenement.metaevt IS 'données trouvées à partir de la table public.str_model à un moment donné';


--
-- Name: evt_categorie; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE evt_categorie (
    id integer NOT NULL,
    libelle character varying NOT NULL,
    txtva numeric(5,2) NOT NULL
);


--
-- Name: TABLE evt_categorie; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE evt_categorie IS 'categories d''evenement';


--
-- Name: COLUMN evt_categorie.txtva; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN evt_categorie.txtva IS 'taux de tva à appliquer par défaut';


--
-- Name: evenement_categorie; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW evenement_categorie AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, evt.duree, evt.ages, evt.code, evt.creation, evt.modification, cat.libelle AS catdesc, cat.txtva, evt.metaevt, evt.tarifweb, evt.tarifwebgroup, evt.extradesc, evt.extraspec, evt.imageurl FROM evenement evt, evt_categorie cat WHERE ((evt.categorie = cat.id) AND (evt.categorie IS NOT NULL)) UNION SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, evt.duree, evt.ages, evt.code, evt.creation, evt.modification, NULL::unknown AS catdesc, NULL::unknown AS txtva, evt.metaevt, evt.tarifweb, evt.tarifwebgroup, evt.extradesc, evt.extraspec, evt.imageurl FROM evenement evt WHERE (evt.categorie IS NULL) ORDER BY 19, 5;


--
-- Name: VIEW evenement_categorie; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW evenement_categorie IS 'Liste des organismes avec leur catégorie (qui est à NULL s''ils n''en ont pas)';


--
-- Name: evenement_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE evenement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: evenement_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE evenement_id_seq OWNED BY evenement.id;


--
-- Name: evt_categorie_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE evt_categorie_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: evt_categorie_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE evt_categorie_id_seq OWNED BY evt_categorie.id;


--
-- Name: facture; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE facture (
    id integer NOT NULL,
    transaction bigint NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    accountid bigint NOT NULL
);


--
-- Name: TABLE facture; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE facture IS 'Référencement des factures, pour leur numéro';


--
-- Name: COLUMN facture.id; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN facture.id IS 'numéro de facture sans ''FB'' devant';


--
-- Name: COLUMN facture.transaction; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN facture.transaction IS 'numéro de transaction';


--
-- Name: COLUMN facture.date; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN facture.date IS 'date de sortie de la facture';


--
-- Name: COLUMN facture.accountid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN facture.accountid IS 'account.id';


--
-- Name: facture_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE facture_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: facture_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE facture_id_seq OWNED BY facture.id;


--
-- Name: site; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE site (
    id integer NOT NULL,
    nom character varying(255) NOT NULL,
    adresse text,
    cp character varying(10),
    ville character varying(255),
    pays character varying(255) DEFAULT 'France'::character varying NOT NULL,
    regisseur integer,
    organisme integer,
    dimensions_salle integer[],
    dimensions_scene integer[],
    noir_possible boolean,
    gradins boolean,
    amperage integer,
    description text,
    modification timestamp with time zone DEFAULT now() NOT NULL,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    active boolean DEFAULT true NOT NULL,
    dynamicplan text,
    capacity integer
);


--
-- Name: TABLE site; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE site IS 'Lieux où peuvent se dérouler des manifestations';


--
-- Name: COLUMN site.nom; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.nom IS 'nom du lieu (ex: MPT de Penhars)';


--
-- Name: COLUMN site.cp; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.cp IS 'code postal de la ville';


--
-- Name: COLUMN site.ville; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.ville IS 'ville où se situe le lieu';


--
-- Name: COLUMN site.pays; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.pays IS 'Pays où se situe le lieu';


--
-- Name: COLUMN site.regisseur; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.regisseur IS 'public.org_personne.id';


--
-- Name: COLUMN site.organisme; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.organisme IS 'public.organsme.id';


--
-- Name: COLUMN site.dimensions_salle; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.dimensions_salle IS 'L x P x H';


--
-- Name: COLUMN site.dimensions_scene; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.dimensions_scene IS 'L x P x H';


--
-- Name: COLUMN site.noir_possible; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.noir_possible IS 'peut-on faire le noir dans la salle ?';


--
-- Name: COLUMN site.gradins; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.gradins IS 'y a-t-il des gradins dans la salle ?';


--
-- Name: COLUMN site.amperage; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.amperage IS 'ampérage disponible';


--
-- Name: COLUMN site.modification; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.modification IS 'date de dernière modification';


--
-- Name: COLUMN site.creation; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.creation IS 'date de creation';


--
-- Name: COLUMN site.active; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN site.active IS 'la salle est utilisable';


--
-- Name: info_resa; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW info_resa AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, manif.duree, evt.ages, evt.code, evt.creation, evt.modification, evt.catdesc, manif.id AS manifid, manif.date, manif.jauge, manif.description AS manifdesc, site.id AS siteid, site.nom AS sitenom, site.ville, site.cp, manif.plnum, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (NOT (resa.transaction IN (SELECT preselled.transaction FROM preselled))))) AS commandes, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE ((resa.manifid = manif.id) AND (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AS resas, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (resa.transaction IN (SELECT preselled.transaction FROM preselled)))) AS preresas, evt.txtva AS deftva, manif.txtva, colors.libelle AS colorname, colors.color FROM evenement_categorie evt, manifestation manif, site, colors WHERE (((evt.id = manif.evtid) AND (site.id = manif.siteid)) AND ((colors.id = manif.colorid) OR ((colors.id IS NULL) AND (manif.colorid IS NULL)))) ORDER BY evt.catdesc, evt.nom, manif.date;


--
-- Name: VIEW info_resa; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW info_resa IS 'permet d''avoir d''un coup toutes les informations de réservation nécessaires';


--
-- Name: manif_organisation; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE manif_organisation (
    orgid integer NOT NULL,
    manifid integer NOT NULL
);


--
-- Name: TABLE manif_organisation; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE manif_organisation IS 'Organisation d''une manifestation';


--
-- Name: COLUMN manif_organisation.orgid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manif_organisation.orgid IS 'public.organisme.id';


--
-- Name: COLUMN manif_organisation.manifid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN manif_organisation.manifid IS 'manifestation.id';


--
-- Name: manifestation_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE manifestation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: manifestation_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE manifestation_id_seq OWNED BY manifestation.id;


--
-- Name: manifestation_tarifs_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE manifestation_tarifs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: manifestation_tarifs_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE manifestation_tarifs_id_seq OWNED BY manifestation_tarifs.id;


--
-- Name: masstickets; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE masstickets (
    transaction bigint NOT NULL,
    nb integer NOT NULL,
    tarifid integer NOT NULL,
    reduc integer DEFAULT 0 NOT NULL,
    manifid integer NOT NULL,
    printed integer DEFAULT 0 NOT NULL,
    nb_orig integer NOT NULL,
    CONSTRAINT masstickets_nb_positive CHECK ((NOT (nb < 0))),
    CONSTRAINT masstickets_printed_positive CHECK ((NOT (printed < 0)))
)
INHERITS (reservation);


--
-- Name: TABLE masstickets; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE masstickets IS 'permet d''avoir un mémo des tickets imprimés en masse';


--
-- Name: COLUMN masstickets.transaction; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN masstickets.transaction IS 'transaction.id';


--
-- Name: COLUMN masstickets.nb; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN masstickets.nb IS 'nombre de billets à éditer';


--
-- Name: COLUMN masstickets.tarifid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN masstickets.tarifid IS 'tarif.id';


--
-- Name: COLUMN masstickets.reduc; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN masstickets.reduc IS 'réduction octroyée (comme dans reservation_pre)';


--
-- Name: COLUMN masstickets.manifid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN masstickets.manifid IS 'manifestation.id';


--
-- Name: COLUMN masstickets.nb_orig; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN masstickets.nb_orig IS 'Nombre original de billets du dépot';


--
-- Name: modepaiement; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE modepaiement (
    id integer NOT NULL,
    libelle character varying(63) NOT NULL,
    numcompte character varying(30) NOT NULL
);


--
-- Name: TABLE modepaiement; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE modepaiement IS 'Modes de paiement disponibles pour la billeterie';


--
-- Name: COLUMN modepaiement.libelle; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN modepaiement.libelle IS 'description';


--
-- Name: COLUMN modepaiement.numcompte; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN modepaiement.numcompte IS 'numéro de compte comptable correspondant';


--
-- Name: modepaiement_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE modepaiement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: modepaiement_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE modepaiement_id_seq OWNED BY modepaiement.id;


--
-- Name: paiement; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE paiement (
    id bigint NOT NULL,
    modepaiementid integer NOT NULL,
    montant numeric(11,3) NOT NULL,
    transaction bigint NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    sysdate timestamp with time zone DEFAULT now()
);


--
-- Name: TABLE paiement; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE paiement IS 'règlement d''une partie d''un "reglement"';


--
-- Name: COLUMN paiement.modepaiementid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN paiement.modepaiementid IS 'modepaiement.id';


--
-- Name: COLUMN paiement.montant; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN paiement.montant IS 'montant du paiement';


--
-- Name: COLUMN paiement.transaction; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN paiement.transaction IS 'numéro de transaction';


--
-- Name: COLUMN paiement.date; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN paiement.date IS 'date du paiement';


--
-- Name: COLUMN paiement.sysdate; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN paiement.sysdate IS 'Date d''intervention pour le paiement courant, sans aucun lien avec la date de valeur';


--
-- Name: paid; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW paid AS
    SELECT paiement.transaction, sum(paiement.montant) AS prix FROM paiement GROUP BY paiement.transaction;


--
-- Name: VIEW paid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW paid IS 'Regroupe les transactions et les paiements liés';


--
-- Name: paiement_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE paiement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: paiement_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE paiement_id_seq OWNED BY paiement.id;


--
-- Name: personne_evtbackup; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE personne_evtbackup (
    id integer NOT NULL,
    personneid integer NOT NULL,
    fctorgid integer,
    evenement character varying(255) NOT NULL,
    date timestamp with time zone NOT NULL
);


--
-- Name: TABLE personne_evtbackup; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE personne_evtbackup IS 'Table reprenant les évènements des années précédentes où la personne a été enregistrée';


--
-- Name: COLUMN personne_evtbackup.personneid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN personne_evtbackup.personneid IS 'public.personne.id';


--
-- Name: COLUMN personne_evtbackup.fctorgid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN personne_evtbackup.fctorgid IS 'public.org_personne.id';


--
-- Name: COLUMN personne_evtbackup.evenement; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN personne_evtbackup.evenement IS 'nom de l''evenement (anciennement billeterie.evenement.nom';


--
-- Name: COLUMN personne_evtbackup.date; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN personne_evtbackup.date IS 'date de la manifestation (anciennement billeterie.manifestation.date)';


--
-- Name: personne_evtbackup_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE personne_evtbackup_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: personne_evtbackup_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE personne_evtbackup_id_seq OWNED BY personne_evtbackup.id;


--
-- Name: reservation_cur_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE reservation_cur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: reservation_cur_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE reservation_cur_id_seq OWNED BY reservation_cur.id;


--
-- Name: rights; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    level integer DEFAULT 0 NOT NULL
);


SET search_path = public, pg_catalog;

SET default_with_oids = true;

--
-- Name: entite; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE entite (
    id integer NOT NULL,
    nom character varying(127) NOT NULL,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    modification timestamp with time zone DEFAULT now() NOT NULL,
    adresse text,
    cp character varying(10),
    ville character varying(255),
    pays character varying(255) DEFAULT 'France'::character varying,
    email character varying(255),
    npai boolean DEFAULT false NOT NULL,
    active boolean DEFAULT true NOT NULL
);


--
-- Name: TABLE entite; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE entite IS 'entités liées à l''organisme (personnes ou organismes)';


--
-- Name: COLUMN entite.cp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN entite.cp IS 'code postal de l''adresse';


--
-- Name: COLUMN entite.email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN entite.email IS 'adresse email';


--
-- Name: COLUMN entite.active; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN entite.active IS 'permet de "supprimer" une entité dans l''application tout en gardant sa trace...';


--
-- Name: entite_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE entite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: entite_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE entite_id_seq OWNED BY entite.id;


--
-- Name: fonction; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE fonction (
    id integer NOT NULL,
    libelle character varying(127) NOT NULL
);


--
-- Name: TABLE fonction; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE fonction IS 'Fonction liant une personne à un organisme (avec son intitulé exact par exemple)';


--
-- Name: COLUMN fonction.libelle; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN fonction.libelle IS 'intitulé type, servant dans les extractions par exemple';


--
-- Name: org_categorie; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE org_categorie (
    id integer NOT NULL,
    libelle character varying(255) NOT NULL
);


--
-- Name: TABLE org_categorie; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE org_categorie IS 'categories regroupant des sous catégories d''organismes';


--
-- Name: org_personne; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE org_personne (
    id integer NOT NULL,
    personneid bigint NOT NULL,
    organismeid bigint NOT NULL,
    fonction character varying(255),
    email character varying(255),
    service character varying(255),
    type integer,
    telephone character varying(40),
    description text
);


--
-- Name: TABLE org_personne; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE org_personne IS 'liaison entre des personnes et des organismes, au titre d''une fonction dans ledit organisme';


--
-- Name: COLUMN org_personne.personneid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.personneid IS 'personne.id';


--
-- Name: COLUMN org_personne.organismeid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.organismeid IS 'organisme.id';


--
-- Name: COLUMN org_personne.fonction; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.fonction IS 'fonction au titre de laquelle une personne est liée à un organisme';


--
-- Name: COLUMN org_personne.email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.email IS 'email de la personne dans l''organisme';


--
-- Name: COLUMN org_personne.service; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.service IS 'Service dans l''organisme où travaille la personne';


--
-- Name: COLUMN org_personne.type; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.type IS 'fonction.id : type de fonction';


--
-- Name: COLUMN org_personne.telephone; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.telephone IS 'téléphone professionel d''une personne liée à un organisme';


--
-- Name: COLUMN org_personne.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN org_personne.description IS 'description du pro';


--
-- Name: organisme; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE organisme (
    url character varying(255),
    categorie integer,
    description text
)
INHERITS (entite);


--
-- Name: TABLE organisme; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE organisme IS 'structures en contact avec l''organisme';


--
-- Name: COLUMN organisme.description; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN organisme.description IS 'Description de l''organisme';


--
-- Name: organisme_categorie; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW organisme_categorie AS
    SELECT organisme.id, organisme.nom, organisme.creation, organisme.modification, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.npai, organisme.active, organisme.url, organisme.categorie, org_categorie.libelle AS catdesc, organisme.description FROM organisme, org_categorie WHERE (((organisme.categorie = org_categorie.id) AND (organisme.categorie IS NOT NULL)) AND (organisme.active = true)) UNION SELECT organisme.id, organisme.nom, organisme.creation, organisme.modification, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.npai, organisme.active, organisme.url, NULL::unknown AS categorie, NULL::unknown AS catdesc, organisme.description FROM organisme WHERE ((organisme.categorie IS NULL) AND (organisme.active = true)) ORDER BY 14, 2;


--
-- Name: VIEW organisme_categorie; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW organisme_categorie IS 'Liste des organismes avec leur catégorie (qui est à NULL s''ils n''en ont pas)';


--
-- Name: personne; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE personne (
    prenom character varying(255),
    titre character varying(24),
    description text
)
INHERITS (entite);


--
-- Name: TABLE personne; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE personne IS 'contacts de l''organisme';


--
-- Name: personne_properso; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW personne_properso AS
    (((SELECT DISTINCT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id AS orgid, organisme.nom AS orgnom, organisme.categorie AS orgcat, organisme.adresse AS orgadr, organisme.cp AS orgcp, organisme.ville AS orgville, organisme.pays AS orgpays, organisme.email AS orgemail, organisme.url AS orgurl, organisme.description AS orgdesc, org_personne.service, org_personne.id AS fctorgid, fonction.id AS fctid, fonction.libelle AS fcttype, org_personne.fonction AS fctdesc, org_personne.email AS proemail, org_personne.telephone AS protel, organisme.catdesc AS orgcatdesc, personne.description FROM organisme_categorie organisme, personne, org_personne, fonction WHERE ((((personne.id = org_personne.personneid) AND (organisme.id = org_personne.organismeid)) AND (fonction.id = org_personne.type)) AND (org_personne.type IS NOT NULL)) ORDER BY personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id, organisme.nom, organisme.categorie, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.url, organisme.description, org_personne.service, org_personne.id, fonction.id, fonction.libelle, org_personne.fonction, org_personne.email, org_personne.telephone, organisme.catdesc, personne.description) UNION (SELECT DISTINCT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id AS orgid, organisme.nom AS orgnom, organisme.categorie AS orgcat, organisme.adresse AS orgadr, organisme.cp AS orgcp, organisme.ville AS orgville, organisme.pays AS orgpays, organisme.email AS orgemail, organisme.url AS orgurl, organisme.description AS orgdesc, org_personne.service, org_personne.id AS fctorgid, NULL::integer AS fctid, NULL::text AS fcttype, org_personne.fonction AS fctdesc, org_personne.email AS proemail, org_personne.telephone AS protel, organisme.catdesc AS orgcatdesc, personne.description FROM organisme_categorie organisme, personne, org_personne WHERE (((personne.id = org_personne.personneid) AND (organisme.id = org_personne.organismeid)) AND (org_personne.type IS NULL)) ORDER BY personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id, organisme.nom, organisme.categorie, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.url, organisme.description, org_personne.service, org_personne.id, NULL::integer, NULL::text, org_personne.fonction, org_personne.email, org_personne.telephone, organisme.catdesc, personne.description)) UNION SELECT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgcat, NULL::unknown AS orgadr, NULL::unknown AS orgcp, NULL::unknown AS orgville, NULL::unknown AS orgpays, NULL::unknown AS orgemail, NULL::unknown AS orgurl, NULL::unknown AS orgdesc, NULL::unknown AS service, NULL::unknown AS fctorgid, NULL::unknown AS fctid, NULL::unknown AS fcttype, NULL::unknown AS fctdesc, NULL::unknown AS proemail, NULL::unknown AS protel, NULL::unknown AS orgcatdesc, personne.description FROM personne) UNION SELECT NULL::unknown AS id, NULL::unknown AS nom, NULL::unknown AS creation, NULL::unknown AS modification, NULL::unknown AS adresse, NULL::unknown AS cp, NULL::unknown AS ville, NULL::unknown AS pays, NULL::unknown AS email, NULL::unknown AS npai, NULL::unknown AS active, NULL::unknown AS prenom, NULL::unknown AS titre, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgcat, NULL::unknown AS orgadr, NULL::unknown AS orgcp, NULL::unknown AS orgville, NULL::unknown AS orgpays, NULL::unknown AS orgemail, NULL::unknown AS orgurl, NULL::unknown AS orgdesc, NULL::unknown AS service, NULL::unknown AS fctorgid, NULL::unknown AS fctid, NULL::unknown AS fcttype, NULL::unknown AS fctdesc, NULL::unknown AS proemail, NULL::unknown AS protel, NULL::unknown AS orgcatdesc, NULL::unknown AS description ORDER BY 2, 12, 15, 27, 28, 24;


SET search_path = billeterie, pg_catalog;

--
-- Name: site_datas; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW site_datas AS
    ((SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, organisme.id AS orgid, organisme.nom AS orgnom, organisme.ville AS orgville, personne.id AS persid, personne.titre AS perstitre, personne.nom AS persnom, personne.prenom AS persprenom, personne.protel AS perstel FROM site, public.organisme, public.personne_properso personne WHERE ((organisme.id = site.organisme) AND (personne.id = site.regisseur)) UNION SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgville, personne.id AS persid, personne.titre AS perstitre, personne.nom AS persnom, personne.prenom AS persprenom, personne.protel AS perstel FROM site, public.personne_properso personne WHERE ((site.organisme IS NULL) AND (personne.id = site.regisseur))) UNION SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, organisme.id AS orgid, organisme.nom AS orgnom, organisme.ville AS orgville, NULL::unknown AS persid, NULL::unknown AS perstitre, NULL::unknown AS persnom, NULL::unknown AS persprenom, NULL::unknown AS perstel FROM site, public.organisme WHERE ((organisme.id = site.organisme) AND (site.regisseur IS NULL))) UNION SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgville, NULL::unknown AS persid, NULL::unknown AS perstitre, NULL::unknown AS persnom, NULL::unknown AS persprenom, NULL::unknown AS perstel FROM site WHERE ((site.organisme IS NULL) AND (site.regisseur IS NULL)) ORDER BY 2, 5;


--
-- Name: site_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE site_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: site_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE site_id_seq OWNED BY site.id;


SET default_with_oids = false;

--
-- Name: site_plnum; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE site_plnum (
    id integer NOT NULL,
    plname character varying(8) NOT NULL,
    siteid integer NOT NULL,
    onmapx character varying(6) NOT NULL,
    onmapy character varying(6) NOT NULL,
    width character varying(6) NOT NULL,
    height character varying(6) NOT NULL,
    comment text
);


--
-- Name: site_plnum_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE site_plnum_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: site_plnum_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE site_plnum_id_seq OWNED BY site_plnum.id;


--
-- Name: tarif_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE tarif_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: tarif_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE tarif_id_seq OWNED BY tarif.id;


--
-- Name: tickets2pay; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW tickets2pay AS
    SELECT ticket.transaction, ticket.manifid, ticket.nb, ticket.tarif AS key, ticket.reduc, ticket.prix, ticket.prixspec FROM resumetickets2print ticket WHERE ((ticket.canceled = false) AND (ticket.printed = true));


--
-- Name: VIEW tickets2pay; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW tickets2pay IS 'donne l''ensemble des tickets qui ont été imprimés et qu''il reste à payer
(deprecated, préférer "SELECT *,getprice(manifid,tarif) FROM tickets2print_bytransac() WHERE printed = true AND canceled = false")
(vue très lente, à cause de resumetickets2print)';


--
-- Name: topay; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW topay AS
    SELECT resa.transaction, sum((((getprice(resa.manifid, resa.tarifid))::double precision * (- (1)::double precision)) * ((((resa.annul)::integer * 2) - 1))::double precision)) AS prix FROM reservation_cur, reservation_pre resa WHERE ((NOT reservation_cur.canceled) AND (reservation_cur.resa_preid = resa.id)) GROUP BY resa.transaction;


--
-- Name: VIEW topay; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON VIEW topay IS 'regroupe les transactions et la somme des prix des billets liés';


--
-- Name: transaction; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE transaction (
    id bigint NOT NULL,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    accountid bigint NOT NULL,
    personneid bigint,
    fctorgid bigint,
    translinked bigint,
    dematerialized boolean DEFAULT false NOT NULL,
    blocked boolean DEFAULT false
);


--
-- Name: COLUMN transaction.id; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN transaction.id IS 'numéro de transaction';


--
-- Name: COLUMN transaction.accountid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN transaction.accountid IS 'account.id';


--
-- Name: COLUMN transaction.personneid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN transaction.personneid IS 'personne.id';


--
-- Name: COLUMN transaction.fctorgid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN transaction.fctorgid IS 'org_personne.id';


--
-- Name: COLUMN transaction.translinked; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN transaction.translinked IS 'La transaction courante est issue d''une autre transaction dont cette colonne est le numéro.';


--
-- Name: transaction_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE transaction_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: transaction_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE transaction_id_seq OWNED BY transaction.id;


SET search_path = public, pg_catalog;

SET default_with_oids = true;

--
-- Name: object; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE object (
    id bigint NOT NULL,
    name character varying(128) NOT NULL,
    description text
);


--
-- Name: TABLE object; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE object IS 'Base table for a unified scape for every objects';


--
-- Name: object_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE object_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: object_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE object_id_seq OWNED BY object.id;


--
-- Name: account; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account (
    login character varying(32) NOT NULL,
    password character varying(32) NOT NULL,
    active boolean DEFAULT true NOT NULL,
    expire date,
    level integer DEFAULT 0 NOT NULL,
    email character varying(255)
)
INHERITS (object);


--
-- Name: COLUMN account.level; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN account.level IS 'Niveau de droits octroyé... dépend de l''application. Ici >= 10 : admin ; >= 5 : possibilité de modifier des fiches ; < 5 : consultation simple';


--
-- Name: COLUMN account.email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN account.email IS 'email de l''utilisateur';


SET search_path = billeterie, pg_catalog;

--
-- Name: waitingdepots; Type: VIEW; Schema: billeterie; Owner: -
--

CREATE VIEW waitingdepots AS
    SELECT DISTINCT contingeant.transaction, contingeant.closed, contingeant.date, personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, personne.orgid, personne.orgnom, personne.orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, personne.orgcatdesc, account.name, (SELECT count(*) AS count FROM reservation_pre WHERE ((reservation_pre.transaction = transaction.id) AND (NOT reservation_pre.annul))) AS total, (SELECT count(*) AS count FROM reservation_pre, tarif WHERE ((((reservation_pre.transaction = transaction.id) AND (reservation_pre.tarifid = tarif.id)) AND tarif.contingeant) AND (NOT reservation_pre.annul))) AS cont, (SELECT sum(masstickets.nb) AS nb FROM masstickets WHERE (masstickets.transaction = transaction.id)) AS masstick FROM public.personne_properso personne, contingeant, public.account, transaction WHERE ((((((personne.fctorgid = contingeant.fctorgid) OR ((personne.fctorgid IS NULL) AND (contingeant.fctorgid IS NULL))) AND (personne.id = contingeant.personneid)) AND (account.id = contingeant.accountid)) AND (transaction.id = contingeant.transaction)) AND ((SELECT count(*) AS count FROM reservation_pre WHERE ((reservation_pre.transaction = transaction.id) AND (NOT reservation_pre.annul))) > 0)) ORDER BY contingeant.transaction DESC, personne.nom, personne.prenom, personne.orgnom, personne.id, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.titre, personne.orgid, personne.orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, personne.orgcatdesc, account.name, (SELECT count(*) AS count FROM reservation_pre WHERE ((reservation_pre.transaction = transaction.id) AND (NOT reservation_pre.annul))), (SELECT count(*) AS count FROM reservation_pre, tarif WHERE ((((reservation_pre.transaction = transaction.id) AND (reservation_pre.tarifid = tarif.id)) AND tarif.contingeant) AND (NOT reservation_pre.annul))), (SELECT sum(masstickets.nb) AS nb FROM masstickets WHERE (masstickets.transaction = transaction.id)), contingeant.closed, contingeant.date;


SET search_path = pro, pg_catalog;

SET default_with_oids = false;

--
-- Name: contingentspro; Type: TABLE; Schema: pro; Owner: -; Tablespace: 
--

CREATE TABLE contingentspro (
    fctorgid integer NOT NULL
);


--
-- Name: TABLE contingentspro; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON TABLE contingentspro IS 'Personnes dont les contingents sont pris en compte dans le module "pro"';


--
-- Name: modepaiement; Type: TABLE; Schema: pro; Owner: -; Tablespace: 
--

CREATE TABLE modepaiement (
    letter character(1) NOT NULL,
    libelle character varying(255) NOT NULL
);


--
-- Name: TABLE modepaiement; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON TABLE modepaiement IS 'Cette table définit les modes de paiement possibles pour le module "pro"';


--
-- Name: COLUMN modepaiement.letter; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON COLUMN modepaiement.letter IS 'Lettre symbole du libelle';


--
-- Name: COLUMN modepaiement.libelle; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON COLUMN modepaiement.libelle IS 'Libelle "human readable"';


--
-- Name: params; Type: TABLE; Schema: pro; Owner: -; Tablespace: 
--

CREATE TABLE params (
    name character varying(255) NOT NULL,
    value character varying(255) NOT NULL
);


--
-- Name: TABLE params; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON TABLE params IS 'Cette table définit des variables de paramétrage pour le module "pro"';


--
-- Name: COLUMN params.name; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON COLUMN params.name IS 'Nom du paramètre';


--
-- Name: COLUMN params.value; Type: COMMENT; Schema: pro; Owner: -
--

COMMENT ON COLUMN params.value IS 'Valeur du paramètre';


--
-- Name: rights; Type: TABLE; Schema: pro; Owner: -; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    level integer DEFAULT 0 NOT NULL
);


--
-- Name: roadmap; Type: TABLE; Schema: pro; Owner: -; Tablespace: 
--

CREATE TABLE roadmap (
    fctorgid bigint NOT NULL,
    manifid integer NOT NULL,
    paid boolean DEFAULT false NOT NULL,
    modepaiement character(1),
    date timestamp with time zone DEFAULT now() NOT NULL,
    id integer NOT NULL
);


--
-- Name: roadmap_id_seq; Type: SEQUENCE; Schema: pro; Owner: -
--

CREATE SEQUENCE roadmap_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: roadmap_id_seq; Type: SEQUENCE OWNED BY; Schema: pro; Owner: -
--

ALTER SEQUENCE roadmap_id_seq OWNED BY roadmap.id;


SET search_path = public, pg_catalog;

--
-- Name: child; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE child (
    id integer NOT NULL,
    personneid integer NOT NULL,
    birth integer NOT NULL,
    name text
);


--
-- Name: TABLE child; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE child IS 'Permet de définir l''âge des enfants d''un contact';


--
-- Name: COLUMN child.personneid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN child.personneid IS 'personne.id';


--
-- Name: COLUMN child.birth; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN child.birth IS 'year of birth';


--
-- Name: COLUMN child.name; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN child.name IS 'child''s name';


--
-- Name: child_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE child_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: child_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE child_id_seq OWNED BY child.id;


--
-- Name: color; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE color (
    id integer NOT NULL,
    libelle character varying(127) NOT NULL,
    color character varying(6) NOT NULL
);


--
-- Name: TABLE color; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE color IS 'Permet de donner des couleurs aux manifestations. attention à choisir des couleurs assez claires, proches du blanc.';


--
-- Name: COLUMN color.color; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN color.color IS 'Valeur RGB de type HTML de la couleur correspondant au nom';


--
-- Name: color_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE color_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: color_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE color_id_seq OWNED BY color.id;


--
-- Name: email; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE email (
    id integer NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    accountid bigint NOT NULL,
    "from" character varying(255) NOT NULL,
    "to" text NOT NULL,
    bcc text,
    subject text NOT NULL,
    content text NOT NULL,
    full_c text NOT NULL,
    full_h text NOT NULL,
    sent boolean DEFAULT false NOT NULL,
    max_recipient integer DEFAULT 0 NOT NULL
);


--
-- Name: TABLE email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE email IS 'where are recorded all emails sent by the "emailing" tool...';


--
-- Name: email_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE email_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: email_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE email_id_seq OWNED BY email.id;


--
-- Name: fonction_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE fonction_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: fonction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE fonction_id_seq OWNED BY fonction.id;


--
-- Name: groupe; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE groupe (
    id integer NOT NULL,
    nom character varying(255) NOT NULL,
    createur bigint,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    modification timestamp with time zone DEFAULT now() NOT NULL,
    description text
);


--
-- Name: TABLE groupe; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE groupe IS 'groupes de personnes créés à partir du requêteur';


--
-- Name: COLUMN groupe.id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe.id IS 'id du groupe permettant de reconsituer le nom système de la view représentant le groupe ("grp_`id`")';


--
-- Name: COLUMN groupe.nom; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe.nom IS 'nom usuel du groupe';


--
-- Name: COLUMN groupe.createur; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe.createur IS 'lien vers le createur du groupe (account.id)';


--
-- Name: groupe_andreq; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE groupe_andreq (
    id integer NOT NULL,
    fctid integer,
    orgid integer,
    orgcat integer,
    cp character varying(10),
    ville character varying(255),
    npai boolean DEFAULT false,
    email boolean DEFAULT false,
    adresse boolean DEFAULT false,
    infcreation date,
    infmodification date,
    supcreation date,
    supmodification date,
    groupid integer NOT NULL,
    grpinc integer[],
    childmax integer,
    childmin integer
);


--
-- Name: TABLE groupe_andreq; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE groupe_andreq IS 'chaque ligne correspond à un groupe de ET logiques qui, regroupées en OU logiques, définissent un groupe...';


--
-- Name: COLUMN groupe_andreq.fctid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.fctid IS 'fonction.id';


--
-- Name: COLUMN groupe_andreq.orgid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.orgid IS 'organisme.id';


--
-- Name: COLUMN groupe_andreq.orgcat; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.orgcat IS 'org_categorie.id';


--
-- Name: COLUMN groupe_andreq.cp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.cp IS 'personne.cp LIKE ''cp%'' OR organisme.cp LIKE ''cp%''';


--
-- Name: COLUMN groupe_andreq.ville; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.ville IS 'personne.ville LIKE ''ville%'' OR organisme.ville LIKE ''ville%''';


--
-- Name: COLUMN groupe_andreq.npai; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.npai IS 'personne.npai';


--
-- Name: COLUMN groupe_andreq.email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.email IS 'personne.email IS NULL => true (si une personne N''a PAS d''email)';


--
-- Name: COLUMN groupe_andreq.adresse; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.adresse IS 'personne.adresse IS NULL => true (une personne N''a PAS d''adresse)';


--
-- Name: COLUMN groupe_andreq.infcreation; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.infcreation IS 'personne.creation < infcreation';


--
-- Name: COLUMN groupe_andreq.infmodification; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.infmodification IS 'personne.modification < infmodification';


--
-- Name: COLUMN groupe_andreq.supcreation; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.supcreation IS 'personne.creation >= supcreation';


--
-- Name: COLUMN groupe_andreq.supmodification; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.supmodification IS 'personne.modification >= supmodification';


--
-- Name: COLUMN groupe_andreq.groupid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.groupid IS 'groupe.id';


--
-- Name: COLUMN groupe_andreq.grpinc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.grpinc IS 'inclusion de groupes dans la condition';


--
-- Name: COLUMN groupe_andreq.childmax; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.childmax IS 'date("Y") - childmax >= child.birth';


--
-- Name: COLUMN groupe_andreq.childmin; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_andreq.childmin IS 'date("Y") - childmin <= child.birth';


--
-- Name: groupe_andreq_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE groupe_andreq_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: groupe_andreq_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE groupe_andreq_id_seq OWNED BY groupe_andreq.id;


--
-- Name: groupe_fonctions; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE groupe_fonctions (
    groupid integer NOT NULL,
    fonctionid integer NOT NULL,
    included boolean DEFAULT false NOT NULL,
    info text
);


--
-- Name: TABLE groupe_fonctions; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE groupe_fonctions IS 'Liaison directe entre fonctions au sein d''un organisme et groupe... une fonction est liée à un groupe avec un booléen qui exprime si elle est exclue (false) ou inclue (true).';


--
-- Name: COLUMN groupe_fonctions.groupid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_fonctions.groupid IS 'groupe.id';


--
-- Name: COLUMN groupe_fonctions.fonctionid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_fonctions.fonctionid IS 'org_personne.id';


--
-- Name: COLUMN groupe_fonctions.info; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_fonctions.info IS 'Colonne permettant de stocker des informations subsidiaires';


--
-- Name: groupe_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE groupe_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: groupe_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE groupe_id_seq OWNED BY groupe.id;


--
-- Name: groupe_personnes; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE groupe_personnes (
    groupid integer NOT NULL,
    personneid integer NOT NULL,
    included boolean DEFAULT false NOT NULL,
    info text
);


--
-- Name: TABLE groupe_personnes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE groupe_personnes IS 'Liaison directe entre personnes et groupe... une personne est liée à un groupe avec un booléen qui exprime si elle est exclue (false) ou inclue (true).';


--
-- Name: COLUMN groupe_personnes.groupid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_personnes.groupid IS 'groupe.id';


--
-- Name: COLUMN groupe_personnes.personneid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_personnes.personneid IS 'personne.id';


--
-- Name: COLUMN groupe_personnes.included; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_personnes.included IS 'la personne est incluse dans le groupe ? (si non : elle est exclue)';


--
-- Name: COLUMN groupe_personnes.info; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN groupe_personnes.info IS 'Colonne permettant de stocker des informations subsidiaires';


--
-- Name: login; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE login (
    id integer NOT NULL,
    accountid bigint,
    triedname character varying(127),
    ipaddress character varying(255) NOT NULL,
    success boolean NOT NULL,
    date timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE login; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE login IS 'Loggue tous les accès au logiciel';


--
-- Name: COLUMN login.accountid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN login.accountid IS 'account.id';


--
-- Name: COLUMN login.triedname; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN login.triedname IS 'nom utilisé pour la tentative de connexion';


--
-- Name: login_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE login_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: login_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE login_id_seq OWNED BY login.id;


--
-- Name: new_groupe_fonctions; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE new_groupe_fonctions (
    "?column?" integer,
    fonctionid integer,
    included boolean,
    info text
);


--
-- Name: options; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE options (
    id integer NOT NULL,
    accountid bigint,
    key character varying(127) NOT NULL,
    value character varying(511) NOT NULL
);


--
-- Name: TABLE options; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE options IS 'Options liées aux comptes';


--
-- Name: COLUMN options.accountid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN options.accountid IS 'account.id';


--
-- Name: COLUMN options.key; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN options.key IS 'clé';


--
-- Name: options_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE options_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: options_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE options_id_seq OWNED BY options.id;


--
-- Name: org_categorie_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE org_categorie_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: org_categorie_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE org_categorie_id_seq OWNED BY org_categorie.id;


--
-- Name: org_personne_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE org_personne_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: org_personne_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE org_personne_id_seq OWNED BY org_personne.id;


SET default_with_oids = true;

--
-- Name: telephone; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE telephone (
    id integer NOT NULL,
    entiteid bigint NOT NULL,
    type character varying(127),
    numero character varying(40) NOT NULL
);


--
-- Name: TABLE telephone; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE telephone IS 'numéros de téléphones génériques';


--
-- Name: telephone_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE telephone_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: telephone_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE telephone_id_seq OWNED BY telephone.id;


--
-- Name: telephone_organisme; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE telephone_organisme (
)
INHERITS (telephone);


--
-- Name: TABLE telephone_organisme; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE telephone_organisme IS 'numéros de téléphones des organismes';


--
-- Name: organisme_extractor; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW organisme_extractor AS
    SELECT org.id, org.nom, org.creation, org.modification, org.adresse, org.cp, org.ville, org.pays, org.email, org.npai, org.active, org.url, org.categorie, org.catdesc, org.description, (SELECT telephone_organisme.numero FROM telephone_organisme WHERE (telephone_organisme.entiteid = org.id) ORDER BY telephone_organisme.id LIMIT 1) AS telnum, (SELECT telephone_organisme.type FROM telephone_organisme WHERE (telephone_organisme.entiteid = org.id) ORDER BY telephone_organisme.id LIMIT 1) AS teltype FROM organisme_categorie org;


--
-- Name: VIEW organisme_extractor; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW organisme_extractor IS 'Permet de regrouper toutes les données à extraire d''un seul coup';


--
-- Name: organisme_telephone; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW organisme_telephone AS
    SELECT organisme.id, NULL::unknown AS type, NULL::unknown AS numero FROM organisme WHERE (NOT (organisme.id IN (SELECT telephone_organisme.entiteid FROM telephone_organisme))) UNION SELECT organisme.id, telephone.type, telephone.numero FROM organisme, telephone_organisme telephone WHERE (organisme.id = telephone.entiteid) ORDER BY 1, 2, 3;


--
-- Name: VIEW organisme_telephone; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON VIEW organisme_telephone IS 'Donne chaque organisme avec ses numéros et type de tel, ou chaque personne accompagnées d''un téléphone à double champ "NULL"';


--
-- Name: personne_telephone; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW personne_telephone AS
    SELECT organisme.id, NULL::unknown AS type, NULL::unknown AS numero FROM organisme WHERE (NOT (organisme.id IN (SELECT telephone_organisme.entiteid FROM telephone_organisme))) UNION SELECT organisme.id, telephone.type, telephone.numero FROM organisme, telephone_organisme telephone WHERE (organisme.id = telephone.entiteid) ORDER BY 1, 2, 3;


--
-- Name: personne_extractor; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW personne_extractor AS
    SELECT personne.id, personne.nom, personne.prenom, personne.titre, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.creation, personne.modification, (SELECT personne_telephone.numero FROM personne_telephone WHERE (personne.id = personne_telephone.id) LIMIT 1) AS telnum, (SELECT personne_telephone.type FROM personne_telephone WHERE (personne.id = personne_telephone.id) LIMIT 1) AS teltype, personne.orgid, personne.orgnom, personne.orgcatdesc AS orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, (SELECT organisme_telephone.numero FROM organisme_telephone WHERE (personne.orgid = organisme_telephone.id) LIMIT 1) AS orgtelnum, (SELECT organisme_telephone.type FROM organisme_telephone WHERE (personne.orgid = organisme_telephone.id) LIMIT 1) AS orgteltype FROM personne_properso personne;


SET default_with_oids = false;

--
-- Name: rights; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    level integer DEFAULT 0 NOT NULL
);


--
-- Name: str_model; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE str_model (
    str character varying(255) NOT NULL,
    usage character varying(63) NOT NULL
);


--
-- Name: COLUMN str_model.usage; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN str_model.usage IS 'ce à quoi va servir le champ précédent';


SET default_with_oids = true;

--
-- Name: telephone_personne; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE telephone_personne (
)
INHERITS (telephone);


--
-- Name: TABLE telephone_personne; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE telephone_personne IS 'numéros de téléphones des personnes';


SET search_path = sco, pg_catalog;

SET default_with_oids = false;

--
-- Name: entry; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE entry (
    id integer NOT NULL,
    tabpersid integer NOT NULL,
    tabmanifid integer NOT NULL,
    valid boolean DEFAULT false NOT NULL,
    secondary boolean DEFAULT false NOT NULL
);


--
-- Name: TABLE entry; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON TABLE entry IS 'Tickets voulus pour chq entrée';


--
-- Name: entry_id_seq; Type: SEQUENCE; Schema: sco; Owner: -
--

CREATE SEQUENCE entry_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: entry_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: -
--

ALTER SEQUENCE entry_id_seq OWNED BY entry.id;


--
-- Name: params; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE params (
    name character varying(255) NOT NULL,
    value character varying(255) NOT NULL
);


--
-- Name: TABLE params; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON TABLE params IS 'Cette table définit des variables de paramétrage pour le module "pro"';


--
-- Name: COLUMN params.name; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN params.name IS 'Nom du paramètre';


--
-- Name: COLUMN params.value; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN params.value IS 'Valeur du paramètre';


--
-- Name: responsable; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE responsable (
    fctorgid integer NOT NULL
);


--
-- Name: TABLE responsable; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON TABLE responsable IS 'Who is responsible for the contingents reserved for this module';


--
-- Name: COLUMN responsable.fctorgid; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN responsable.fctorgid IS 'fonction.id';


--
-- Name: rights; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    level integer DEFAULT 0 NOT NULL
);


--
-- Name: tableau; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE tableau (
    id integer NOT NULL,
    accountid bigint NOT NULL,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    modification timestamp with time zone DEFAULT now() NOT NULL
);


--
-- Name: TABLE tableau; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON TABLE tableau IS 'Unité de base permettant la création des diverses dimensions du tableau de gestion des groupes et des scolaires';


--
-- Name: COLUMN tableau.accountid; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN tableau.accountid IS 'public.account.id';


--
-- Name: tableau_id_seq; Type: SEQUENCE; Schema: sco; Owner: -
--

CREATE SEQUENCE tableau_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: tableau_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: -
--

ALTER SEQUENCE tableau_id_seq OWNED BY tableau.id;


--
-- Name: tableau_manif; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE tableau_manif (
    id integer NOT NULL,
    tableauid integer NOT NULL,
    manifid integer NOT NULL
);


--
-- Name: tableau_manif_id_seq; Type: SEQUENCE; Schema: sco; Owner: -
--

CREATE SEQUENCE tableau_manif_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: tableau_manif_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: -
--

ALTER SEQUENCE tableau_manif_id_seq OWNED BY tableau_manif.id;


--
-- Name: tableau_personne; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE tableau_personne (
    id integer NOT NULL,
    tableauid integer NOT NULL,
    personneid bigint NOT NULL,
    fctorgid bigint,
    transposed integer,
    conftext text,
    confirmed boolean DEFAULT false NOT NULL,
    comment text
);


--
-- Name: TABLE tableau_personne; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON TABLE tableau_personne IS 'Rempli les colonnes du tableau, les manifestations';


--
-- Name: COLUMN tableau_personne.conftext; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN tableau_personne.conftext IS 'Permet de mettre un commentaire à propos de la confirmation de réception de la facture';


--
-- Name: COLUMN tableau_personne.confirmed; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN tableau_personne.confirmed IS 'Indique si les interlocuteurs ont confirmé la réception de leur facture';


--
-- Name: COLUMN tableau_personne.comment; Type: COMMENT; Schema: sco; Owner: -
--

COMMENT ON COLUMN tableau_personne.comment IS 'Commentaire sur la personne (projet prioritaire par exemple)';


--
-- Name: tableau_personne_id_seq; Type: SEQUENCE; Schema: sco; Owner: -
--

CREATE SEQUENCE tableau_personne_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: tableau_personne_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: -
--

ALTER SEQUENCE tableau_personne_id_seq OWNED BY tableau_personne.id;


--
-- Name: ticket; Type: TABLE; Schema: sco; Owner: -; Tablespace: 
--

CREATE TABLE ticket (
    id integer NOT NULL,
    entryid integer NOT NULL,
    nb integer NOT NULL,
    tarifid integer NOT NULL,
    reduc integer NOT NULL
);


--
-- Name: ticket_id_seq; Type: SEQUENCE; Schema: sco; Owner: -
--

CREATE SEQUENCE ticket_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: ticket_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: -
--

ALTER SEQUENCE ticket_id_seq OWNED BY ticket.id;


SET search_path = billeterie, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE color ALTER COLUMN id SET DEFAULT nextval('color_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE evenement ALTER COLUMN id SET DEFAULT nextval('evenement_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE evt_categorie ALTER COLUMN id SET DEFAULT nextval('evt_categorie_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE facture ALTER COLUMN id SET DEFAULT nextval('facture_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE manifestation ALTER COLUMN id SET DEFAULT nextval('manifestation_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE manifestation_tarifs ALTER COLUMN id SET DEFAULT nextval('manifestation_tarifs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE modepaiement ALTER COLUMN id SET DEFAULT nextval('modepaiement_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE paiement ALTER COLUMN id SET DEFAULT nextval('paiement_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE personne_evtbackup ALTER COLUMN id SET DEFAULT nextval('personne_evtbackup_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE preselled ALTER COLUMN id SET DEFAULT nextval('preselled_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE reservation ALTER COLUMN id SET DEFAULT nextval('reservation_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE reservation_cur ALTER COLUMN id SET DEFAULT nextval('reservation_cur_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE site ALTER COLUMN id SET DEFAULT nextval('site_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE site_plnum ALTER COLUMN id SET DEFAULT nextval('site_plnum_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE tarif ALTER COLUMN id SET DEFAULT nextval('tarif_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE transaction ALTER COLUMN id SET DEFAULT nextval('transaction_id_seq'::regclass);


SET search_path = pro, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: pro; Owner: -
--

ALTER TABLE roadmap ALTER COLUMN id SET DEFAULT nextval('roadmap_id_seq'::regclass);


SET search_path = public, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE child ALTER COLUMN id SET DEFAULT nextval('child_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE color ALTER COLUMN id SET DEFAULT nextval('color_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE email ALTER COLUMN id SET DEFAULT nextval('email_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE entite ALTER COLUMN id SET DEFAULT nextval('entite_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE fonction ALTER COLUMN id SET DEFAULT nextval('fonction_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE groupe ALTER COLUMN id SET DEFAULT nextval('groupe_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE groupe_andreq ALTER COLUMN id SET DEFAULT nextval('groupe_andreq_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE login ALTER COLUMN id SET DEFAULT nextval('login_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE object ALTER COLUMN id SET DEFAULT nextval('object_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE options ALTER COLUMN id SET DEFAULT nextval('options_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE org_categorie ALTER COLUMN id SET DEFAULT nextval('org_categorie_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE org_personne ALTER COLUMN id SET DEFAULT nextval('org_personne_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE telephone ALTER COLUMN id SET DEFAULT nextval('telephone_id_seq'::regclass);


SET search_path = sco, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: -
--

ALTER TABLE entry ALTER COLUMN id SET DEFAULT nextval('entry_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: -
--

ALTER TABLE tableau ALTER COLUMN id SET DEFAULT nextval('tableau_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: -
--

ALTER TABLE tableau_manif ALTER COLUMN id SET DEFAULT nextval('tableau_manif_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: -
--

ALTER TABLE tableau_personne ALTER COLUMN id SET DEFAULT nextval('tableau_personne_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: -
--

ALTER TABLE ticket ALTER COLUMN id SET DEFAULT nextval('ticket_id_seq'::regclass);


SET search_path = billeterie, pg_catalog;

--
-- Name: bdc_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY bdc
    ADD CONSTRAINT bdc_pkey PRIMARY KEY (id);


--
-- Name: bdc_transaction_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY bdc
    ADD CONSTRAINT bdc_transaction_key UNIQUE (transaction);


--
-- Name: color_libelle_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY color
    ADD CONSTRAINT color_libelle_key UNIQUE (libelle);


--
-- Name: color_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY color
    ADD CONSTRAINT color_pkey PRIMARY KEY (id);


--
-- Name: evenement_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY evenement
    ADD CONSTRAINT evenement_pkey PRIMARY KEY (id);


--
-- Name: evt_cat_libelle_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY evt_categorie
    ADD CONSTRAINT evt_cat_libelle_key UNIQUE (libelle);


--
-- Name: evt_cat_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY evt_categorie
    ADD CONSTRAINT evt_cat_pkey PRIMARY KEY (id);


--
-- Name: facture_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY facture
    ADD CONSTRAINT facture_pkey PRIMARY KEY (id);


--
-- Name: facture_transaction_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY facture
    ADD CONSTRAINT facture_transaction_key UNIQUE (transaction);


--
-- Name: lieu_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY site
    ADD CONSTRAINT lieu_pkey PRIMARY KEY (id);


--
-- Name: manif_organisation_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY manif_organisation
    ADD CONSTRAINT manif_organisation_pkey PRIMARY KEY (orgid, manifid);


--
-- Name: manifestation_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY manifestation
    ADD CONSTRAINT manifestation_pkey PRIMARY KEY (id);


--
-- Name: manifestation_tarifs_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY manifestation_tarifs
    ADD CONSTRAINT manifestation_tarifs_pkey PRIMARY KEY (id);


--
-- Name: masstickets_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY masstickets
    ADD CONSTRAINT masstickets_pkey PRIMARY KEY (id);


--
-- Name: masstickets_transaction_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY masstickets
    ADD CONSTRAINT masstickets_transaction_key UNIQUE (transaction, tarifid, reduc, manifid);


--
-- Name: modepaiement_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY modepaiement
    ADD CONSTRAINT modepaiement_pkey PRIMARY KEY (id);


--
-- Name: paiement_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY paiement
    ADD CONSTRAINT paiement_pkey PRIMARY KEY (id);


--
-- Name: personne_evtbackup_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY personne_evtbackup
    ADD CONSTRAINT personne_evtbackup_pkey PRIMARY KEY (id);


--
-- Name: preselled_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY preselled
    ADD CONSTRAINT preselled_pkey PRIMARY KEY (id);


--
-- Name: preselled_transaction_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY preselled
    ADD CONSTRAINT preselled_transaction_key UNIQUE (transaction);


--
-- Name: reservation_cur_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY reservation_cur
    ADD CONSTRAINT reservation_cur_pkey PRIMARY KEY (id);


--
-- Name: reservation_pre_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_pkey PRIMARY KEY (id);


--
-- Name: reservation_pre_plnum_ukey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_plnum_ukey UNIQUE (manifid, plnum, annul);


--
-- Name: site_plnum_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY site_plnum
    ADD CONSTRAINT site_plnum_pkey PRIMARY KEY (id);


--
-- Name: site_plnum_siteid_ukey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY site_plnum
    ADD CONSTRAINT site_plnum_siteid_ukey UNIQUE (plname, siteid);


--
-- Name: tarif_key_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tarif
    ADD CONSTRAINT tarif_key_key UNIQUE (key, date);


--
-- Name: tarif_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tarif
    ADD CONSTRAINT tarif_pkey PRIMARY KEY (id);


--
-- Name: transaction_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT transaction_pkey PRIMARY KEY (id);


SET search_path = pro, pg_catalog;

--
-- Name: contingentspro_pkey; Type: CONSTRAINT; Schema: pro; Owner: -; Tablespace: 
--

ALTER TABLE ONLY contingentspro
    ADD CONSTRAINT contingentspro_pkey PRIMARY KEY (fctorgid);


--
-- Name: modepaiement_pkey; Type: CONSTRAINT; Schema: pro; Owner: -; Tablespace: 
--

ALTER TABLE ONLY modepaiement
    ADD CONSTRAINT modepaiement_pkey PRIMARY KEY (letter);


--
-- Name: params_pkey; Type: CONSTRAINT; Schema: pro; Owner: -; Tablespace: 
--

ALTER TABLE ONLY params
    ADD CONSTRAINT params_pkey PRIMARY KEY (name);


--
-- Name: rights_pkey; Type: CONSTRAINT; Schema: pro; Owner: -; Tablespace: 
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_pkey PRIMARY KEY (id);


--
-- Name: roadmap_pkey; Type: CONSTRAINT; Schema: pro; Owner: -; Tablespace: 
--

ALTER TABLE ONLY roadmap
    ADD CONSTRAINT roadmap_pkey PRIMARY KEY (id);


SET search_path = public, pg_catalog;

--
-- Name: accounts_login_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account
    ADD CONSTRAINT accounts_login_key UNIQUE (login);


--
-- Name: accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account
    ADD CONSTRAINT accounts_pkey PRIMARY KEY (id);


--
-- Name: child_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY child
    ADD CONSTRAINT child_pkey PRIMARY KEY (id);


--
-- Name: email_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY email
    ADD CONSTRAINT email_pkey PRIMARY KEY (id);


--
-- Name: entite_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY entite
    ADD CONSTRAINT entite_pkey PRIMARY KEY (id);


--
-- Name: group_andreq_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT group_andreq_pkey PRIMARY KEY (id);


--
-- Name: groupe_fonctions_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY groupe_fonctions
    ADD CONSTRAINT groupe_fonctions_pkey PRIMARY KEY (groupid, fonctionid, included);


--
-- Name: groupe_nom_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY groupe
    ADD CONSTRAINT groupe_nom_key UNIQUE (nom, createur);


--
-- Name: groupe_personnes_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY groupe_personnes
    ADD CONSTRAINT groupe_personnes_pkey PRIMARY KEY (groupid, personneid, included);


--
-- Name: groupe_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY groupe
    ADD CONSTRAINT groupe_pkey PRIMARY KEY (id);


--
-- Name: manifestation_login_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY login
    ADD CONSTRAINT manifestation_login_pkey PRIMARY KEY (id);


--
-- Name: options_accountid_key; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_accountid_key UNIQUE (accountid, key);


--
-- Name: options_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_pkey PRIMARY KEY (id);


--
-- Name: org_categorie_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY org_categorie
    ADD CONSTRAINT org_categorie_pkey PRIMARY KEY (id);


--
-- Name: org_fonction_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY fonction
    ADD CONSTRAINT org_fonction_pkey PRIMARY KEY (id);


--
-- Name: org_personne_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_pkey PRIMARY KEY (id);


--
-- Name: organisme_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY organisme
    ADD CONSTRAINT organisme_pkey PRIMARY KEY (id);


--
-- Name: personne_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY personne
    ADD CONSTRAINT personne_pkey PRIMARY KEY (id);


--
-- Name: str_model_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY str_model
    ADD CONSTRAINT str_model_pkey PRIMARY KEY (str, usage);


--
-- Name: telephone_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY telephone
    ADD CONSTRAINT telephone_pkey PRIMARY KEY (id);


SET search_path = sco, pg_catalog;

--
-- Name: entry_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_pkey PRIMARY KEY (id);


--
-- Name: entry_ukey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_ukey UNIQUE (tabpersid, tabmanifid);


--
-- Name: responsable_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY responsable
    ADD CONSTRAINT responsable_pkey PRIMARY KEY (fctorgid);


--
-- Name: rights_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_pkey PRIMARY KEY (id);


--
-- Name: tableau_manif_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tableau_manif
    ADD CONSTRAINT tableau_manif_pkey PRIMARY KEY (id);


--
-- Name: tableau_personne_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_pkey PRIMARY KEY (id);


--
-- Name: tableau_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tableau
    ADD CONSTRAINT tableau_pkey PRIMARY KEY (id);


--
-- Name: ticket_pkey; Type: CONSTRAINT; Schema: sco; Owner: -; Tablespace: 
--

ALTER TABLE ONLY ticket
    ADD CONSTRAINT ticket_pkey PRIMARY KEY (id);


SET search_path = billeterie, pg_catalog;

--
-- Name: reservation_cur_preid; Type: INDEX; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE INDEX reservation_cur_preid ON reservation_cur USING btree (resa_preid);


--
-- Name: reservation_pre_transaction; Type: INDEX; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE INDEX reservation_pre_transaction ON reservation_pre USING btree (transaction);


SET search_path = public, pg_catalog;

--
-- Name: login_index; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX login_index ON account USING btree (login);


SET search_path = billeterie, pg_catalog;

--
-- Name: manifestation_trigger; Type: TRIGGER; Schema: billeterie; Owner: -
--

CREATE TRIGGER manifestation_trigger
    BEFORE UPDATE ON manifestation
    FOR EACH ROW
    EXECUTE PROCEDURE manif_update();


--
-- Name: bdc_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY bdc
    ADD CONSTRAINT bdc_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: contingeant_fctorgid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY contingeant
    ADD CONSTRAINT contingeant_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: contingeant_personneid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY contingeant
    ADD CONSTRAINT contingeant_personneid_fkey FOREIGN KEY (personneid) REFERENCES public.personne(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: contingeant_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY contingeant
    ADD CONSTRAINT contingeant_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: evenement_organisme2_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY evenement
    ADD CONSTRAINT evenement_organisme2_fkey FOREIGN KEY (organisme2) REFERENCES public.organisme(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: evenement_organisme3_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY evenement
    ADD CONSTRAINT evenement_organisme3_fkey FOREIGN KEY (organisme3) REFERENCES public.organisme(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: facture_accountid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY facture
    ADD CONSTRAINT facture_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: facture_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY facture
    ADD CONSTRAINT facture_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: manif_organisation_manifid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY manif_organisation
    ADD CONSTRAINT manif_organisation_manifid_fkey FOREIGN KEY (manifid) REFERENCES manifestation(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: manifestation_colorid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY manifestation
    ADD CONSTRAINT manifestation_colorid_fkey FOREIGN KEY (colorid) REFERENCES color(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: manifestation_evtid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY manifestation
    ADD CONSTRAINT manifestation_evtid_fkey FOREIGN KEY (evtid) REFERENCES evenement(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: manifestation_lieuid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY manifestation
    ADD CONSTRAINT manifestation_lieuid_fkey FOREIGN KEY (siteid) REFERENCES site(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: manifestation_tarifs_manifestationid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY manifestation_tarifs
    ADD CONSTRAINT manifestation_tarifs_manifestationid_fkey FOREIGN KEY (manifestationid) REFERENCES manifestation(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: manifestation_tarifs_tarifid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY manifestation_tarifs
    ADD CONSTRAINT manifestation_tarifs_tarifid_fkey FOREIGN KEY (tarifid) REFERENCES tarif(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: masstickets_manifid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY masstickets
    ADD CONSTRAINT masstickets_manifid_fkey FOREIGN KEY (manifid) REFERENCES manifestation(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: masstickets_tarifid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY masstickets
    ADD CONSTRAINT masstickets_tarifid_fkey FOREIGN KEY (tarifid) REFERENCES tarif(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: masstickets_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY masstickets
    ADD CONSTRAINT masstickets_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: paiement_modepaiementid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY paiement
    ADD CONSTRAINT paiement_modepaiementid_fkey FOREIGN KEY (modepaiementid) REFERENCES modepaiement(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: paiement_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY paiement
    ADD CONSTRAINT paiement_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: personne_evtbackup_fctorgid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY personne_evtbackup
    ADD CONSTRAINT personne_evtbackup_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: personne_evtbackup_personneid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY personne_evtbackup
    ADD CONSTRAINT personne_evtbackup_personneid_fkey FOREIGN KEY (personneid) REFERENCES public.personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: preselled_accountid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY preselled
    ADD CONSTRAINT preselled_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: preselled_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY preselled
    ADD CONSTRAINT preselled_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: reservation_accountid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY reservation
    ADD CONSTRAINT reservation_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: reservation_cur_resa_preid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY reservation_cur
    ADD CONSTRAINT reservation_cur_resa_preid_fkey FOREIGN KEY (resa_preid) REFERENCES reservation_pre(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: reservation_pre_manifid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_manifid_fkey FOREIGN KEY (manifid) REFERENCES manifestation(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: reservation_pre_plnum_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_plnum_fkey FOREIGN KEY (plnum) REFERENCES site_plnum(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: reservation_pre_tarifid_fkey1; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_tarifid_fkey1 FOREIGN KEY (tarifid) REFERENCES tarif(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: reservation_pre_transaction_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_transaction_fkey FOREIGN KEY (transaction) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: site_organisme_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY site
    ADD CONSTRAINT site_organisme_fkey FOREIGN KEY (organisme) REFERENCES public.organisme(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: site_plnum_siteid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY site_plnum
    ADD CONSTRAINT site_plnum_siteid_fkey FOREIGN KEY (siteid) REFERENCES site(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: site_regisseur_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY site
    ADD CONSTRAINT site_regisseur_fkey FOREIGN KEY (regisseur) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: transaction_fctorgid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT transaction_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: transaction_personneid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT transaction_personneid_fkey FOREIGN KEY (personneid) REFERENCES public.personne(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: transaction_translinked_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY transaction
    ADD CONSTRAINT transaction_translinked_fkey FOREIGN KEY (translinked) REFERENCES transaction(id) ON UPDATE CASCADE ON DELETE SET NULL;


SET search_path = pro, pg_catalog;

--
-- Name: contingentspro_fctorgid_fkey; Type: FK CONSTRAINT; Schema: pro; Owner: -
--

ALTER TABLE ONLY contingentspro
    ADD CONSTRAINT contingentspro_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id);


--
-- Name: rights_id_fkey; Type: FK CONSTRAINT; Schema: pro; Owner: -
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_id_fkey FOREIGN KEY (id) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: roadmap_fctorgid_fkey; Type: FK CONSTRAINT; Schema: pro; Owner: -
--

ALTER TABLE ONLY roadmap
    ADD CONSTRAINT roadmap_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: roadmap_modepaiement_fkey; Type: FK CONSTRAINT; Schema: pro; Owner: -
--

ALTER TABLE ONLY roadmap
    ADD CONSTRAINT roadmap_modepaiement_fkey FOREIGN KEY (modepaiement) REFERENCES modepaiement(letter) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = public, pg_catalog;

--
-- Name: child_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY child
    ADD CONSTRAINT child_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: email_accountid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY email
    ADD CONSTRAINT email_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_andreq_fctid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_fctid_fkey FOREIGN KEY (fctid) REFERENCES fonction(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_andreq_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_groupid_fkey FOREIGN KEY (groupid) REFERENCES groupe(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_andreq_orgcat_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_orgcat_fkey FOREIGN KEY (orgcat) REFERENCES org_categorie(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_andreq_orgid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_orgid_fkey FOREIGN KEY (orgid) REFERENCES organisme(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_createur_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe
    ADD CONSTRAINT groupe_createur_fkey FOREIGN KEY (createur) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_fonctions_fonctionid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_fonctions
    ADD CONSTRAINT groupe_fonctions_fonctionid_fkey FOREIGN KEY (fonctionid) REFERENCES org_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_fonctions_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_fonctions
    ADD CONSTRAINT groupe_fonctions_groupid_fkey FOREIGN KEY (groupid) REFERENCES groupe(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_personnes_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_personnes
    ADD CONSTRAINT groupe_personnes_groupid_fkey FOREIGN KEY (groupid) REFERENCES groupe(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_personnes_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY groupe_personnes
    ADD CONSTRAINT groupe_personnes_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: manifestation_login_accountid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY login
    ADD CONSTRAINT manifestation_login_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: options_accountid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: org_personne_organismeid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_organismeid_fkey FOREIGN KEY (organismeid) REFERENCES organisme(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: org_personne_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: org_personne_type_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_type_fkey FOREIGN KEY (type) REFERENCES fonction(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: organisme_categorie_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organisme
    ADD CONSTRAINT organisme_categorie_fkey FOREIGN KEY (categorie) REFERENCES org_categorie(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: telephone_entiteid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY telephone_personne
    ADD CONSTRAINT telephone_entiteid_fkey FOREIGN KEY (entiteid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: telephone_entiteid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY telephone_organisme
    ADD CONSTRAINT telephone_entiteid_fkey FOREIGN KEY (entiteid) REFERENCES organisme(id) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = sco, pg_catalog;

--
-- Name: entry_tabmanifid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_tabmanifid_fkey FOREIGN KEY (tabmanifid) REFERENCES tableau_manif(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entry_tabpersid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_tabpersid_fkey FOREIGN KEY (tabpersid) REFERENCES tableau_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: responsable_fctorgid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY responsable
    ADD CONSTRAINT responsable_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: rights_id_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_id_fkey FOREIGN KEY (id) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tableau_accountid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY tableau
    ADD CONSTRAINT tableau_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tableau_manif_tableauid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY tableau_manif
    ADD CONSTRAINT tableau_manif_tableauid_fkey FOREIGN KEY (tableauid) REFERENCES tableau(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tableau_personne_fctorgid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tableau_personne_personneid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_personneid_fkey FOREIGN KEY (personneid) REFERENCES public.personne(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tableau_personne_tableauid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: -
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_tableauid_fkey FOREIGN KEY (tableauid) REFERENCES tableau(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

