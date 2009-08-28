--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


SET search_path = public, pg_catalog;

--
-- Name: resume_tickets; Type: TYPE; Schema: public; Owner: ttt
--

CREATE TYPE resume_tickets AS (
	"transaction" bigint,
	manifid integer,
	nb bigint,
	tarif character varying,
	reduc integer,
	printed boolean,
	canceled boolean,
	prix numeric,
	prixspec numeric
);


ALTER TYPE public.resume_tickets OWNER TO ttt;

--
-- Name: get_personneid(integer); Type: FUNCTION; Schema: public; Owner: ttt
--

CREATE FUNCTION get_personneid(integer) RETURNS bigint
    AS $_$SELECT personneid AS result FROM org_personne WHERE id = $1;$_$
    LANGUAGE sql STABLE STRICT;


ALTER FUNCTION public.get_personneid(integer) OWNER TO ttt;

--
-- Name: FUNCTION get_personneid(integer); Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON FUNCTION get_personneid(integer) IS 'retourne l''id d''une personne investie de la fonction $1
$1: org_personne.id';


--
-- Name: zeroifnull(bigint); Type: FUNCTION; Schema: public; Owner: ttt
--

CREATE FUNCTION zeroifnull(bigint) RETURNS bigint
    AS $_$BEGIN
IF $1 IS NULL THEN RETURN 0;
ELSE RETURN $1;
END IF;
END;$_$
    LANGUAGE plpgsql IMMUTABLE;


ALTER FUNCTION public.zeroifnull(bigint) OWNER TO ttt;

SET default_tablespace = '';

SET default_with_oids = true;

--
-- Name: entite; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
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


ALTER TABLE public.entite OWNER TO ttt;

--
-- Name: TABLE entite; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE entite IS 'entités liées à l''organisme (personnes ou organismes)';


--
-- Name: COLUMN entite.cp; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN entite.cp IS 'code postal de l''adresse';


--
-- Name: COLUMN entite.email; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN entite.email IS 'adresse email';


--
-- Name: COLUMN entite.active; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN entite.active IS 'permet de "supprimer" une entité dans l''application tout en gardant sa trace...';


--
-- Name: entite_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE entite_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.entite_id_seq OWNER TO ttt;

--
-- Name: entite_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE entite_id_seq OWNED BY entite.id;


--
-- Name: fonction; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE fonction (
    id integer NOT NULL,
    libelle character varying(127) NOT NULL
);


ALTER TABLE public.fonction OWNER TO ttt;

--
-- Name: TABLE fonction; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE fonction IS 'Fonction liant une personne à un organisme (avec son intitulé exact par exemple)';


--
-- Name: COLUMN fonction.libelle; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN fonction.libelle IS 'intitulé type, servant dans les extractions par exemple';


--
-- Name: org_categorie; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE org_categorie (
    id integer NOT NULL,
    libelle character varying(255) NOT NULL
);


ALTER TABLE public.org_categorie OWNER TO ttt;

--
-- Name: TABLE org_categorie; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE org_categorie IS 'categories regroupant des sous catégories d''organismes';


--
-- Name: org_personne; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE org_personne (
    id integer NOT NULL,
    personneid bigint NOT NULL,
    organismeid bigint NOT NULL,
    fonction character varying(255),
    email character varying(255),
    service character varying(255),
    "type" integer,
    telephone character varying(40),
    description text
);


ALTER TABLE public.org_personne OWNER TO ttt;

--
-- Name: TABLE org_personne; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE org_personne IS 'liaison entre des personnes et des organismes, au titre d''une fonction dans ledit organisme';


--
-- Name: COLUMN org_personne.personneid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.personneid IS 'personne.id';


--
-- Name: COLUMN org_personne.organismeid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.organismeid IS 'organisme.id';


--
-- Name: COLUMN org_personne.fonction; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.fonction IS 'fonction au titre de laquelle une personne est liée à un organisme';


--
-- Name: COLUMN org_personne.email; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.email IS 'email de la personne dans l''organisme';


--
-- Name: COLUMN org_personne.service; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.service IS 'Service dans l''organisme où travaille la personne';


--
-- Name: COLUMN org_personne."type"; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne."type" IS 'fonction.id : type de fonction';


--
-- Name: COLUMN org_personne.telephone; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.telephone IS 'téléphone professionel d''une personne liée à un organisme';


--
-- Name: COLUMN org_personne.description; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN org_personne.description IS 'description du pro';


--
-- Name: organisme; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE organisme (
    url character varying(255),
    categorie integer,
    description text
)
INHERITS (entite);


ALTER TABLE public.organisme OWNER TO ttt;

--
-- Name: TABLE organisme; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE organisme IS 'structures en contact avec l''organisme';


--
-- Name: COLUMN organisme.description; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN organisme.description IS 'Description de l''organisme';


--
-- Name: organisme_categorie; Type: VIEW; Schema: public; Owner: ttt
--

CREATE VIEW organisme_categorie AS
    SELECT organisme.id, organisme.nom, organisme.creation, organisme.modification, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.npai, organisme.active, organisme.url, organisme.categorie, org_categorie.libelle AS catdesc, organisme.description FROM organisme, org_categorie WHERE (((organisme.categorie = org_categorie.id) AND (organisme.categorie IS NOT NULL)) AND (organisme.active = true)) UNION SELECT organisme.id, organisme.nom, organisme.creation, organisme.modification, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.npai, organisme.active, organisme.url, NULL::"unknown" AS categorie, NULL::"unknown" AS catdesc, organisme.description FROM organisme WHERE ((organisme.categorie IS NULL) AND (organisme.active = true)) ORDER BY 14, 2;


ALTER TABLE public.organisme_categorie OWNER TO ttt;

--
-- Name: VIEW organisme_categorie; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON VIEW organisme_categorie IS 'Liste des organismes avec leur catégorie (qui est à NULL s''ils n''en ont pas)';


--
-- Name: personne; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE personne (
    prenom character varying(255),
    titre character varying(24)
)
INHERITS (entite);


ALTER TABLE public.personne OWNER TO ttt;

--
-- Name: TABLE personne; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE personne IS 'contacts de l''organisme';


--
-- Name: personne_properso; Type: VIEW; Schema: public; Owner: ttt
--

CREATE VIEW personne_properso AS
    (((SELECT DISTINCT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id AS orgid, organisme.nom AS orgnom, organisme.categorie AS orgcat, organisme.adresse AS orgadr, organisme.cp AS orgcp, organisme.ville AS orgville, organisme.pays AS orgpays, organisme.email AS orgemail, organisme.url AS orgurl, organisme.description AS orgdesc, org_personne.service, org_personne.id AS fctorgid, fonction.id AS fctid, fonction.libelle AS fcttype, org_personne.fonction AS fctdesc, org_personne.email AS proemail, org_personne.telephone AS protel, organisme.catdesc AS orgcatdesc, org_personne.description FROM organisme_categorie organisme, personne, org_personne, fonction WHERE ((((personne.id = org_personne.personneid) AND (organisme.id = org_personne.organismeid)) AND (fonction.id = org_personne."type")) AND (org_personne."type" IS NOT NULL)) ORDER BY personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id, organisme.nom, organisme.categorie, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.url, organisme.description, org_personne.service, org_personne.id, fonction.id, fonction.libelle, org_personne.fonction, org_personne.email, org_personne.telephone, organisme.catdesc, org_personne.description) UNION (SELECT DISTINCT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id AS orgid, organisme.nom AS orgnom, organisme.categorie AS orgcat, organisme.adresse AS orgadr, organisme.cp AS orgcp, organisme.ville AS orgville, organisme.pays AS orgpays, organisme.email AS orgemail, organisme.url AS orgurl, organisme.description AS orgdesc, org_personne.service, org_personne.id AS fctorgid, NULL::integer AS fctid, NULL::text AS fcttype, org_personne.fonction AS fctdesc, org_personne.email AS proemail, org_personne.telephone AS protel, organisme.catdesc AS orgcatdesc, org_personne.description FROM organisme_categorie organisme, personne, org_personne WHERE (((personne.id = org_personne.personneid) AND (organisme.id = org_personne.organismeid)) AND (org_personne."type" IS NULL)) ORDER BY personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id, organisme.nom, organisme.categorie, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.url, organisme.description, org_personne.service, org_personne.id, NULL::integer, NULL::text, org_personne.fonction, org_personne.email, org_personne.telephone, organisme.catdesc, org_personne.description)) UNION SELECT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, NULL::"unknown" AS orgid, NULL::"unknown" AS orgnom, NULL::"unknown" AS orgcat, NULL::"unknown" AS orgadr, NULL::"unknown" AS orgcp, NULL::"unknown" AS orgville, NULL::"unknown" AS orgpays, NULL::"unknown" AS orgemail, NULL::"unknown" AS orgurl, NULL::"unknown" AS orgdesc, NULL::"unknown" AS service, NULL::"unknown" AS fctorgid, NULL::"unknown" AS fctid, NULL::"unknown" AS fcttype, NULL::"unknown" AS fctdesc, NULL::"unknown" AS proemail, NULL::"unknown" AS protel, NULL::"unknown" AS orgcatdesc, NULL::"unknown" AS description FROM personne) UNION SELECT NULL::"unknown" AS id, NULL::"unknown" AS nom, NULL::"unknown" AS creation, NULL::"unknown" AS modification, NULL::"unknown" AS adresse, NULL::"unknown" AS cp, NULL::"unknown" AS ville, NULL::"unknown" AS pays, NULL::"unknown" AS email, NULL::"unknown" AS npai, NULL::"unknown" AS active, NULL::"unknown" AS prenom, NULL::"unknown" AS titre, NULL::"unknown" AS orgid, NULL::"unknown" AS orgnom, NULL::"unknown" AS orgcat, NULL::"unknown" AS orgadr, NULL::"unknown" AS orgcp, NULL::"unknown" AS orgville, NULL::"unknown" AS orgpays, NULL::"unknown" AS orgemail, NULL::"unknown" AS orgurl, NULL::"unknown" AS orgdesc, NULL::"unknown" AS service, NULL::"unknown" AS fctorgid, NULL::"unknown" AS fctid, NULL::"unknown" AS fcttype, NULL::"unknown" AS fctdesc, NULL::"unknown" AS proemail, NULL::"unknown" AS protel, NULL::"unknown" AS orgcatdesc, NULL::"unknown" AS description ORDER BY 2, 12, 15, 27, 28, 24;


ALTER TABLE public.personne_properso OWNER TO ttt;

--
-- Name: VIEW personne_properso; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON VIEW personne_properso IS 'permet d''accéder à toutes les personnes de l''annuaire qu''elles soient pro ou non, qu''elles aient des fonctions au sein d''un organisme ou non';


--
-- Name: object; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE "object" (
    id bigint NOT NULL,
    name character varying(128) NOT NULL,
    description text
);


ALTER TABLE public."object" OWNER TO ttt;

--
-- Name: TABLE "object"; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE "object" IS 'Base table for a unified scape for every objects';


--
-- Name: object_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE object_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.object_id_seq OWNER TO ttt;

--
-- Name: object_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE object_id_seq OWNED BY "object".id;


--
-- Name: account; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE account (
    "login" character varying(32) NOT NULL,
    "password" character varying(32) NOT NULL,
    active boolean DEFAULT true NOT NULL,
    expire date,
    "level" integer DEFAULT 0 NOT NULL,
    email character varying(255)
)
INHERITS ("object");


ALTER TABLE public.account OWNER TO ttt;

--
-- Name: COLUMN account."level"; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN account."level" IS 'Niveau de droits octroyé... dépend de l''application. Ici >= 10 : admin ; >= 5 : possibilité de modifier des fiches ; < 5 : consultation simple';


--
-- Name: COLUMN account.email; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN account.email IS 'email de l''utilisateur';


SET default_with_oids = false;

--
-- Name: child; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE child (
    id integer NOT NULL,
    personneid integer NOT NULL,
    birth integer NOT NULL,
    name text
);


ALTER TABLE public.child OWNER TO ttt;

--
-- Name: TABLE child; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE child IS 'Permet de définir l''âge des enfants d''un contact';


--
-- Name: COLUMN child.personneid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN child.personneid IS 'personne.id';


--
-- Name: COLUMN child.birth; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN child.birth IS 'year of birth';


--
-- Name: COLUMN child.name; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN child.name IS 'child''s name';


--
-- Name: child_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE child_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.child_id_seq OWNER TO ttt;

--
-- Name: child_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE child_id_seq OWNED BY child.id;


--
-- Name: color; Type: TABLE; Schema: public; Owner: beta; Tablespace: 
--

CREATE TABLE color (
    id integer NOT NULL,
    libelle character varying(127) NOT NULL,
    color character varying(6) NOT NULL
);


ALTER TABLE public.color OWNER TO beta;

--
-- Name: TABLE color; Type: COMMENT; Schema: public; Owner: beta
--

COMMENT ON TABLE color IS 'Permet de donner des couleurs aux manifestations. attention à choisir des couleurs assez claires, proches du blanc.';


--
-- Name: COLUMN color.color; Type: COMMENT; Schema: public; Owner: beta
--

COMMENT ON COLUMN color.color IS 'Valeur RGB de type HTML de la couleur correspondant au nom';


--
-- Name: color_id_seq; Type: SEQUENCE; Schema: public; Owner: beta
--

CREATE SEQUENCE color_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.color_id_seq OWNER TO beta;

--
-- Name: color_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: beta
--

ALTER SEQUENCE color_id_seq OWNED BY color.id;


--
-- Name: fonction_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE fonction_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.fonction_id_seq OWNER TO ttt;

--
-- Name: fonction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE fonction_id_seq OWNED BY fonction.id;


--
-- Name: groupe; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE groupe (
    id integer NOT NULL,
    nom character varying(255) NOT NULL,
    createur bigint,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    modification timestamp with time zone DEFAULT now() NOT NULL,
    description text
);


ALTER TABLE public.groupe OWNER TO ttt;

--
-- Name: TABLE groupe; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE groupe IS 'groupes de personnes créés à partir du requêteur';


--
-- Name: COLUMN groupe.id; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe.id IS 'id du groupe permettant de reconsituer le nom système de la view représentant le groupe ("grp_`id`")';


--
-- Name: COLUMN groupe.nom; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe.nom IS 'nom usuel du groupe';


--
-- Name: COLUMN groupe.createur; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe.createur IS 'lien vers le createur du groupe (account.id)';


--
-- Name: groupe_andreq; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
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


ALTER TABLE public.groupe_andreq OWNER TO ttt;

--
-- Name: TABLE groupe_andreq; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE groupe_andreq IS 'chaque ligne correspond à un groupe de ET logiques qui, regroupées en OU logiques, définissent un groupe...';


--
-- Name: COLUMN groupe_andreq.fctid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.fctid IS 'fonction.id';


--
-- Name: COLUMN groupe_andreq.orgid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.orgid IS 'organisme.id';


--
-- Name: COLUMN groupe_andreq.orgcat; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.orgcat IS 'org_categorie.id';


--
-- Name: COLUMN groupe_andreq.cp; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.cp IS 'personne.cp LIKE ''cp%'' OR organisme.cp LIKE ''cp%''';


--
-- Name: COLUMN groupe_andreq.ville; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.ville IS 'personne.ville LIKE ''ville%'' OR organisme.ville LIKE ''ville%''';


--
-- Name: COLUMN groupe_andreq.npai; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.npai IS 'personne.npai';


--
-- Name: COLUMN groupe_andreq.email; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.email IS 'personne.email IS NULL => true (si une personne N''a PAS d''email)';


--
-- Name: COLUMN groupe_andreq.adresse; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.adresse IS 'personne.adresse IS NULL => true (une personne N''a PAS d''adresse)';


--
-- Name: COLUMN groupe_andreq.infcreation; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.infcreation IS 'personne.creation < infcreation';


--
-- Name: COLUMN groupe_andreq.infmodification; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.infmodification IS 'personne.modification < infmodification';


--
-- Name: COLUMN groupe_andreq.supcreation; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.supcreation IS 'personne.creation >= supcreation';


--
-- Name: COLUMN groupe_andreq.supmodification; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.supmodification IS 'personne.modification >= supmodification';


--
-- Name: COLUMN groupe_andreq.groupid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.groupid IS 'groupe.id';


--
-- Name: COLUMN groupe_andreq.grpinc; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.grpinc IS 'inclusion de groupes dans la condition';


--
-- Name: COLUMN groupe_andreq.childmax; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.childmax IS 'date("Y") - childmax >= child.birth';


--
-- Name: COLUMN groupe_andreq.childmin; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_andreq.childmin IS 'date("Y") - childmin <= child.birth';


--
-- Name: groupe_andreq_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE groupe_andreq_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.groupe_andreq_id_seq OWNER TO ttt;

--
-- Name: groupe_andreq_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE groupe_andreq_id_seq OWNED BY groupe_andreq.id;


--
-- Name: groupe_fonctions; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE groupe_fonctions (
    groupid integer NOT NULL,
    fonctionid integer NOT NULL,
    included boolean DEFAULT false NOT NULL,
    info text
);


ALTER TABLE public.groupe_fonctions OWNER TO ttt;

--
-- Name: TABLE groupe_fonctions; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE groupe_fonctions IS 'Liaison directe entre fonctions au sein d''un organisme et groupe... une fonction est liée à un groupe avec un booléen qui exprime si elle est exclue (false) ou inclue (true).';


--
-- Name: COLUMN groupe_fonctions.groupid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_fonctions.groupid IS 'groupe.id';


--
-- Name: COLUMN groupe_fonctions.fonctionid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_fonctions.fonctionid IS 'org_personne.id';


--
-- Name: COLUMN groupe_fonctions.info; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_fonctions.info IS 'Colonne permettant de stocker des informations subsidiaires';


--
-- Name: groupe_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE groupe_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.groupe_id_seq OWNER TO ttt;

--
-- Name: groupe_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE groupe_id_seq OWNED BY groupe.id;


--
-- Name: groupe_personnes; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE groupe_personnes (
    groupid integer NOT NULL,
    personneid integer NOT NULL,
    included boolean DEFAULT false NOT NULL,
    info text
);


ALTER TABLE public.groupe_personnes OWNER TO ttt;

--
-- Name: TABLE groupe_personnes; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE groupe_personnes IS 'Liaison directe entre personnes et groupe... une personne est liée à un groupe avec un booléen qui exprime si elle est exclue (false) ou inclue (true).';


--
-- Name: COLUMN groupe_personnes.groupid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_personnes.groupid IS 'groupe.id';


--
-- Name: COLUMN groupe_personnes.personneid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_personnes.personneid IS 'personne.id';


--
-- Name: COLUMN groupe_personnes.included; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_personnes.included IS 'la personne est incluse dans le groupe ? (si non : elle est exclue)';


--
-- Name: COLUMN groupe_personnes.info; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN groupe_personnes.info IS 'Colonne permettant de stocker des informations subsidiaires';


--
-- Name: login; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE "login" (
    id integer NOT NULL,
    accountid bigint,
    triedname character varying(127),
    ipaddress character varying(255) NOT NULL,
    success boolean NOT NULL,
    date timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public."login" OWNER TO ttt;

--
-- Name: TABLE "login"; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE "login" IS 'Loggue tous les accès au logiciel';


--
-- Name: COLUMN "login".accountid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN "login".accountid IS 'account.id';


--
-- Name: COLUMN "login".triedname; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN "login".triedname IS 'nom utilisé pour la tentative de connexion';


--
-- Name: login_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE login_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.login_id_seq OWNER TO ttt;

--
-- Name: login_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE login_id_seq OWNED BY "login".id;


--
-- Name: options; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE options (
    id integer NOT NULL,
    accountid bigint NOT NULL,
    "key" character varying(127) NOT NULL,
    value character varying(127) NOT NULL
);


ALTER TABLE public.options OWNER TO ttt;

--
-- Name: TABLE options; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE options IS 'Options liées aux comptes';


--
-- Name: COLUMN options.accountid; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN options.accountid IS 'account.id';


--
-- Name: COLUMN options."key"; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN options."key" IS 'clé';


--
-- Name: options_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE options_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.options_id_seq OWNER TO ttt;

--
-- Name: options_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE options_id_seq OWNED BY options.id;


--
-- Name: org_categorie_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE org_categorie_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.org_categorie_id_seq OWNER TO ttt;

--
-- Name: org_categorie_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE org_categorie_id_seq OWNED BY org_categorie.id;


--
-- Name: org_personne_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE org_personne_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.org_personne_id_seq OWNER TO ttt;

--
-- Name: org_personne_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE org_personne_id_seq OWNED BY org_personne.id;


SET default_with_oids = true;

--
-- Name: telephone; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE telephone (
    id integer NOT NULL,
    entiteid bigint NOT NULL,
    "type" character varying(127),
    numero character varying(40) NOT NULL
);


ALTER TABLE public.telephone OWNER TO ttt;

--
-- Name: TABLE telephone; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE telephone IS 'numéros de téléphones génériques';


--
-- Name: telephone_id_seq; Type: SEQUENCE; Schema: public; Owner: ttt
--

CREATE SEQUENCE telephone_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.telephone_id_seq OWNER TO ttt;

--
-- Name: telephone_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ttt
--

ALTER SEQUENCE telephone_id_seq OWNED BY telephone.id;


--
-- Name: telephone_organisme; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE telephone_organisme (
)
INHERITS (telephone);


ALTER TABLE public.telephone_organisme OWNER TO ttt;

--
-- Name: TABLE telephone_organisme; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE telephone_organisme IS 'numéros de téléphones des organismes';


--
-- Name: organisme_extractor; Type: VIEW; Schema: public; Owner: ttt
--

CREATE VIEW organisme_extractor AS
    SELECT org.id, org.nom, org.creation, org.modification, org.adresse, org.cp, org.ville, org.pays, org.email, org.npai, org.active, org.url, org.categorie, org.catdesc, org.description, (SELECT telephone_organisme.numero FROM telephone_organisme WHERE (telephone_organisme.entiteid = org.id) ORDER BY telephone_organisme.id LIMIT 1) AS telnum, (SELECT telephone_organisme."type" FROM telephone_organisme WHERE (telephone_organisme.entiteid = org.id) ORDER BY telephone_organisme.id LIMIT 1) AS teltype FROM organisme_categorie org;


ALTER TABLE public.organisme_extractor OWNER TO ttt;

--
-- Name: VIEW organisme_extractor; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON VIEW organisme_extractor IS 'Permet de regrouper toutes les données à extraire d''un seul coup';


--
-- Name: organisme_telephone; Type: VIEW; Schema: public; Owner: ttt
--

CREATE VIEW organisme_telephone AS
    SELECT organisme.id, NULL::"unknown" AS "type", NULL::"unknown" AS numero FROM organisme WHERE (NOT (organisme.id IN (SELECT telephone_organisme.entiteid FROM telephone_organisme))) UNION SELECT organisme.id, telephone."type", telephone.numero FROM organisme, telephone_organisme telephone WHERE (organisme.id = telephone.entiteid) ORDER BY 1, 2, 3;


ALTER TABLE public.organisme_telephone OWNER TO ttt;

--
-- Name: VIEW organisme_telephone; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON VIEW organisme_telephone IS 'Donne chaque organisme avec ses numéros et type de tel, ou chaque personne accompagnées d''un téléphone à double champ "NULL"';


--
-- Name: telephone_personne; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE telephone_personne (
)
INHERITS (telephone);


ALTER TABLE public.telephone_personne OWNER TO ttt;

--
-- Name: TABLE telephone_personne; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON TABLE telephone_personne IS 'numéros de téléphones des personnes';


--
-- Name: personne_telephone; Type: VIEW; Schema: public; Owner: ttt
--

CREATE VIEW personne_telephone AS
    SELECT personne.id, NULL::"unknown" AS "type", NULL::"unknown" AS numero FROM personne_properso personne WHERE (NOT (personne.id IN (SELECT telephone_personne.entiteid FROM telephone_personne))) UNION SELECT personne.id, telephone."type", telephone.numero FROM personne_properso personne, telephone_personne telephone WHERE (personne.id = telephone.entiteid) ORDER BY 1, 2, 3;


ALTER TABLE public.personne_telephone OWNER TO ttt;

--
-- Name: VIEW personne_telephone; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON VIEW personne_telephone IS 'Donne chaque personne avec ses numéros et type de tel, ou chaque personne accompagnées d''un téléphone à double champ "NULL"';


--
-- Name: personne_extractor; Type: VIEW; Schema: public; Owner: ttt
--

CREATE VIEW personne_extractor AS
    SELECT personne.id, personne.nom, personne.prenom, personne.titre, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.creation, personne.modification, (SELECT personne_telephone.numero FROM personne_telephone WHERE (personne.id = personne_telephone.id) LIMIT 1) AS telnum, (SELECT personne_telephone."type" FROM personne_telephone WHERE (personne.id = personne_telephone.id) LIMIT 1) AS teltype, personne.orgid, personne.orgnom, personne.orgcatdesc AS orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, (SELECT organisme_telephone.numero FROM organisme_telephone WHERE (personne.orgid = organisme_telephone.id) LIMIT 1) AS orgtelnum, (SELECT organisme_telephone."type" FROM organisme_telephone WHERE (personne.orgid = organisme_telephone.id) LIMIT 1) AS orgteltype FROM personne_properso personne;


ALTER TABLE public.personne_extractor OWNER TO ttt;

--
-- Name: VIEW personne_extractor; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON VIEW personne_extractor IS 'View spécialement crée pour l''extracteur';


SET default_with_oids = false;

--
-- Name: rights; Type: TABLE; Schema: public; Owner: beta; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    "level" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.rights OWNER TO beta;

--
-- Name: str_model; Type: TABLE; Schema: public; Owner: ttt; Tablespace: 
--

CREATE TABLE str_model (
    str character varying(255) NOT NULL,
    usage character varying(63) NOT NULL
);


ALTER TABLE public.str_model OWNER TO ttt;

--
-- Name: COLUMN str_model.usage; Type: COMMENT; Schema: public; Owner: ttt
--

COMMENT ON COLUMN str_model.usage IS 'ce à quoi va servir le champ précédent';


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE child ALTER COLUMN id SET DEFAULT nextval('child_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: beta
--

ALTER TABLE color ALTER COLUMN id SET DEFAULT nextval('color_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE entite ALTER COLUMN id SET DEFAULT nextval('entite_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE fonction ALTER COLUMN id SET DEFAULT nextval('fonction_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE groupe ALTER COLUMN id SET DEFAULT nextval('groupe_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE groupe_andreq ALTER COLUMN id SET DEFAULT nextval('groupe_andreq_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE "login" ALTER COLUMN id SET DEFAULT nextval('login_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE "object" ALTER COLUMN id SET DEFAULT nextval('object_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE options ALTER COLUMN id SET DEFAULT nextval('options_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE org_categorie ALTER COLUMN id SET DEFAULT nextval('org_categorie_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE org_personne ALTER COLUMN id SET DEFAULT nextval('org_personne_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ttt
--

ALTER TABLE telephone ALTER COLUMN id SET DEFAULT nextval('telephone_id_seq'::regclass);


--
-- Name: accounts_login_key; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY account
    ADD CONSTRAINT accounts_login_key UNIQUE ("login");


--
-- Name: accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY account
    ADD CONSTRAINT accounts_pkey PRIMARY KEY (id);


--
-- Name: child_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY child
    ADD CONSTRAINT child_pkey PRIMARY KEY (id);


--
-- Name: entite_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY entite
    ADD CONSTRAINT entite_pkey PRIMARY KEY (id);


--
-- Name: group_andreq_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT group_andreq_pkey PRIMARY KEY (id);


--
-- Name: groupe_fonctions_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY groupe_fonctions
    ADD CONSTRAINT groupe_fonctions_pkey PRIMARY KEY (groupid, fonctionid, included);


--
-- Name: groupe_nom_key; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY groupe
    ADD CONSTRAINT groupe_nom_key UNIQUE (nom, createur);


--
-- Name: groupe_personnes_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY groupe_personnes
    ADD CONSTRAINT groupe_personnes_pkey PRIMARY KEY (groupid, personneid, included);


--
-- Name: groupe_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY groupe
    ADD CONSTRAINT groupe_pkey PRIMARY KEY (id);


--
-- Name: manifestation_login_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY "login"
    ADD CONSTRAINT manifestation_login_pkey PRIMARY KEY (id);


--
-- Name: options_accountid_key; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_accountid_key UNIQUE (accountid, "key");


--
-- Name: options_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_pkey PRIMARY KEY (id);


--
-- Name: org_categorie_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY org_categorie
    ADD CONSTRAINT org_categorie_pkey PRIMARY KEY (id);


--
-- Name: org_fonction_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY fonction
    ADD CONSTRAINT org_fonction_pkey PRIMARY KEY (id);


--
-- Name: org_personne_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_pkey PRIMARY KEY (id);


--
-- Name: organisme_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY organisme
    ADD CONSTRAINT organisme_pkey PRIMARY KEY (id);


--
-- Name: personne_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY personne
    ADD CONSTRAINT personne_pkey PRIMARY KEY (id);


--
-- Name: str_model_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY str_model
    ADD CONSTRAINT str_model_pkey PRIMARY KEY (str, usage);


--
-- Name: telephone_pkey; Type: CONSTRAINT; Schema: public; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY telephone
    ADD CONSTRAINT telephone_pkey PRIMARY KEY (id);


--
-- Name: login_index; Type: INDEX; Schema: public; Owner: ttt; Tablespace: 
--

CREATE UNIQUE INDEX login_index ON account USING btree ("login");


--
-- Name: child_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY child
    ADD CONSTRAINT child_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_andreq_fctid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_fctid_fkey FOREIGN KEY (fctid) REFERENCES fonction(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_andreq_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_groupid_fkey FOREIGN KEY (groupid) REFERENCES groupe(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_andreq_orgcat_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_orgcat_fkey FOREIGN KEY (orgcat) REFERENCES org_categorie(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_andreq_orgid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_andreq
    ADD CONSTRAINT groupe_andreq_orgid_fkey FOREIGN KEY (orgid) REFERENCES organisme(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_createur_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe
    ADD CONSTRAINT groupe_createur_fkey FOREIGN KEY (createur) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: groupe_fonctions_fonctionid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_fonctions
    ADD CONSTRAINT groupe_fonctions_fonctionid_fkey FOREIGN KEY (fonctionid) REFERENCES org_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_fonctions_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_fonctions
    ADD CONSTRAINT groupe_fonctions_groupid_fkey FOREIGN KEY (groupid) REFERENCES groupe(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_personnes_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_personnes
    ADD CONSTRAINT groupe_personnes_groupid_fkey FOREIGN KEY (groupid) REFERENCES groupe(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupe_personnes_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY groupe_personnes
    ADD CONSTRAINT groupe_personnes_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: manifestation_login_accountid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY "login"
    ADD CONSTRAINT manifestation_login_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: options_accountid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: org_personne_organismeid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_organismeid_fkey FOREIGN KEY (organismeid) REFERENCES organisme(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: org_personne_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: org_personne_type_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY org_personne
    ADD CONSTRAINT org_personne_type_fkey FOREIGN KEY ("type") REFERENCES fonction(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: organisme_categorie_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY organisme
    ADD CONSTRAINT organisme_categorie_fkey FOREIGN KEY (categorie) REFERENCES org_categorie(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: telephone_entiteid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY telephone_personne
    ADD CONSTRAINT telephone_entiteid_fkey FOREIGN KEY (entiteid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: telephone_entiteid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ttt
--

ALTER TABLE ONLY telephone_organisme
    ADD CONSTRAINT telephone_entiteid_fkey FOREIGN KEY (entiteid) REFERENCES organisme(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: object; Type: ACL; Schema: public; Owner: ttt
--

REVOKE ALL ON TABLE "object" FROM PUBLIC;
REVOKE ALL ON TABLE "object" FROM ttt;
GRANT ALL ON TABLE "object" TO ttt;


--
-- Name: account; Type: ACL; Schema: public; Owner: ttt
--

REVOKE ALL ON TABLE account FROM PUBLIC;
REVOKE ALL ON TABLE account FROM ttt;
GRANT ALL ON TABLE account TO ttt;


--
-- PostgreSQL database dump complete
--

