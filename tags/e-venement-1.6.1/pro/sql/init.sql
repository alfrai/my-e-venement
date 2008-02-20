--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: pro; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA pro;


SET search_path = pro, pg_catalog;

--
-- Name: get_contingeants(integer); Type: FUNCTION; Schema: pro; Owner: -
--

CREATE FUNCTION get_contingeants(integer) RETURNS bigint
    AS $_$SELECT -SUM(annul::integer*2-1) AS RESULT
    FROM billeterie.reservation_pre AS pre, billeterie.contingeant AS cont
    WHERE manifid = $1
      AND pre.transaction = cont.transaction
        AND cont.transaction NOT IN ( SELECT transaction FROM billeterie.masstickets )
        AND cont.fctorgid IN (SELECT fctorgid FROM contingentspro)$_$
    LANGUAGE sql STABLE STRICT;


--
-- Name: is_auto_paid(integer); Type: FUNCTION; Schema: pro; Owner: -
--

CREATE FUNCTION is_auto_paid(integer) RETURNS boolean
    AS $_$
	SELECT billeterie.getprice($1,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1)) = 0 AS RESULT;
$_$
    LANGUAGE sql STABLE STRICT;


SET default_tablespace = '';

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
    "level" integer DEFAULT 0 NOT NULL
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
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: roadmap_id_seq; Type: SEQUENCE OWNED BY; Schema: pro; Owner: -
--

ALTER SEQUENCE roadmap_id_seq OWNED BY roadmap.id;


--
-- Name: id; Type: DEFAULT; Schema: pro; Owner: -
--

ALTER TABLE roadmap ALTER COLUMN id SET DEFAULT nextval('roadmap_id_seq'::regclass);


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
-- Name: roadmap_manifid_fkey; Type: FK CONSTRAINT; Schema: pro; Owner: -
--

ALTER TABLE ONLY roadmap
    ADD CONSTRAINT roadmap_manifid_fkey FOREIGN KEY (manifid) REFERENCES billeterie.manifestation(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: roadmap_modepaiement_fkey; Type: FK CONSTRAINT; Schema: pro; Owner: -
--

ALTER TABLE ONLY roadmap
    ADD CONSTRAINT roadmap_modepaiement_fkey FOREIGN KEY (modepaiement) REFERENCES modepaiement(letter) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

