--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: sco; Type: SCHEMA; Schema: -; Owner: ttt
--

CREATE SCHEMA sco;


ALTER SCHEMA sco OWNER TO ttt;

SET search_path = sco, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: entry; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE entry (
    id integer NOT NULL,
    tabpersid integer NOT NULL,
    tabmanifid integer NOT NULL,
    valid boolean DEFAULT false NOT NULL,
    secondary boolean DEFAULT false NOT NULL
);


ALTER TABLE sco.entry OWNER TO ttt;

--
-- Name: TABLE entry; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON TABLE entry IS 'Tickets voulus pour chq entrée';


--
-- Name: entry_id_seq; Type: SEQUENCE; Schema: sco; Owner: ttt
--

CREATE SEQUENCE entry_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE sco.entry_id_seq OWNER TO ttt;

--
-- Name: entry_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: ttt
--

ALTER SEQUENCE entry_id_seq OWNED BY entry.id;


--
-- Name: params; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE params (
    name character varying(255) NOT NULL,
    value character varying(255) NOT NULL
);


ALTER TABLE sco.params OWNER TO ttt;

--
-- Name: TABLE params; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON TABLE params IS 'Cette table définit des variables de paramétrage pour le module "pro"';


--
-- Name: COLUMN params.name; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN params.name IS 'Nom du paramètre';


--
-- Name: COLUMN params.value; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN params.value IS 'Valeur du paramètre';


--
-- Name: responsable; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE responsable (
    fctorgid integer NOT NULL
);


ALTER TABLE sco.responsable OWNER TO ttt;

--
-- Name: TABLE responsable; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON TABLE responsable IS 'Who is responsible for the contingents reserved for this module';


--
-- Name: COLUMN responsable.fctorgid; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN responsable.fctorgid IS 'fonction.id';


--
-- Name: rights; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    level integer DEFAULT 0 NOT NULL
);


ALTER TABLE sco.rights OWNER TO ttt;

--
-- Name: tableau; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE tableau (
    id integer NOT NULL,
    accountid bigint NOT NULL,
    creation timestamp with time zone DEFAULT now() NOT NULL,
    modification timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE sco.tableau OWNER TO ttt;

--
-- Name: TABLE tableau; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON TABLE tableau IS 'Unité de base permettant la création des diverses dimensions du tableau de gestion des groupes et des scolaires';


--
-- Name: COLUMN tableau.accountid; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN tableau.accountid IS 'public.account.id';


--
-- Name: tableau_id_seq; Type: SEQUENCE; Schema: sco; Owner: ttt
--

CREATE SEQUENCE tableau_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE sco.tableau_id_seq OWNER TO ttt;

--
-- Name: tableau_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: ttt
--

ALTER SEQUENCE tableau_id_seq OWNED BY tableau.id;


--
-- Name: tableau_manif; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE tableau_manif (
    id integer NOT NULL,
    tableauid integer NOT NULL,
    manifid integer NOT NULL
);


ALTER TABLE sco.tableau_manif OWNER TO ttt;

--
-- Name: tableau_manif_id_seq; Type: SEQUENCE; Schema: sco; Owner: ttt
--

CREATE SEQUENCE tableau_manif_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE sco.tableau_manif_id_seq OWNER TO ttt;

--
-- Name: tableau_manif_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: ttt
--

ALTER SEQUENCE tableau_manif_id_seq OWNED BY tableau_manif.id;


--
-- Name: tableau_personne; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
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


ALTER TABLE sco.tableau_personne OWNER TO ttt;

--
-- Name: TABLE tableau_personne; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON TABLE tableau_personne IS 'Rempli les colonnes du tableau, les manifestations';


--
-- Name: COLUMN tableau_personne.conftext; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN tableau_personne.conftext IS 'Permet de mettre un commentaire à propos de la confirmation de réception de la facture';


--
-- Name: COLUMN tableau_personne.confirmed; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN tableau_personne.confirmed IS 'Indique si les interlocuteurs ont confirmé la réception de leur facture';


--
-- Name: COLUMN tableau_personne.comment; Type: COMMENT; Schema: sco; Owner: ttt
--

COMMENT ON COLUMN tableau_personne.comment IS 'Commentaire sur la personne (projet prioritaire par exemple)';


--
-- Name: tableau_personne_id_seq; Type: SEQUENCE; Schema: sco; Owner: ttt
--

CREATE SEQUENCE tableau_personne_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE sco.tableau_personne_id_seq OWNER TO ttt;

--
-- Name: tableau_personne_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: ttt
--

ALTER SEQUENCE tableau_personne_id_seq OWNED BY tableau_personne.id;


--
-- Name: ticket; Type: TABLE; Schema: sco; Owner: ttt; Tablespace: 
--

CREATE TABLE ticket (
    id integer NOT NULL,
    entryid integer NOT NULL,
    nb integer NOT NULL,
    tarifid integer NOT NULL,
    reduc integer NOT NULL
);


ALTER TABLE sco.ticket OWNER TO ttt;

--
-- Name: ticket_id_seq; Type: SEQUENCE; Schema: sco; Owner: ttt
--

CREATE SEQUENCE ticket_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE sco.ticket_id_seq OWNER TO ttt;

--
-- Name: ticket_id_seq; Type: SEQUENCE OWNED BY; Schema: sco; Owner: ttt
--

ALTER SEQUENCE ticket_id_seq OWNED BY ticket.id;


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: ttt
--

ALTER TABLE entry ALTER COLUMN id SET DEFAULT nextval('entry_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: ttt
--

ALTER TABLE tableau ALTER COLUMN id SET DEFAULT nextval('tableau_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: ttt
--

ALTER TABLE tableau_manif ALTER COLUMN id SET DEFAULT nextval('tableau_manif_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: ttt
--

ALTER TABLE tableau_personne ALTER COLUMN id SET DEFAULT nextval('tableau_personne_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sco; Owner: ttt
--

ALTER TABLE ticket ALTER COLUMN id SET DEFAULT nextval('ticket_id_seq'::regclass);


--
-- Name: entry_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_pkey PRIMARY KEY (id);


--
-- Name: entry_ukey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_ukey UNIQUE (tabpersid, tabmanifid);


--
-- Name: responsable_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY responsable
    ADD CONSTRAINT responsable_pkey PRIMARY KEY (fctorgid);


--
-- Name: rights_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_pkey PRIMARY KEY (id);


--
-- Name: tableau_manif_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY tableau_manif
    ADD CONSTRAINT tableau_manif_pkey PRIMARY KEY (id);


--
-- Name: tableau_personne_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_pkey PRIMARY KEY (id);


--
-- Name: tableau_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY tableau
    ADD CONSTRAINT tableau_pkey PRIMARY KEY (id);


--
-- Name: ticket_pkey; Type: CONSTRAINT; Schema: sco; Owner: ttt; Tablespace: 
--

ALTER TABLE ONLY ticket
    ADD CONSTRAINT ticket_pkey PRIMARY KEY (id);


--
-- Name: entry_tabmanifid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_tabmanifid_fkey FOREIGN KEY (tabmanifid) REFERENCES tableau_manif(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: entry_tabpersid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY entry
    ADD CONSTRAINT entry_tabpersid_fkey FOREIGN KEY (tabpersid) REFERENCES tableau_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: responsable_fctorgid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY responsable
    ADD CONSTRAINT responsable_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: rights_id_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_id_fkey FOREIGN KEY (id) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tableau_accountid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY tableau
    ADD CONSTRAINT tableau_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tableau_manif_tableauid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY tableau_manif
    ADD CONSTRAINT tableau_manif_tableauid_fkey FOREIGN KEY (tableauid) REFERENCES tableau(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tableau_personne_fctorgid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_fctorgid_fkey FOREIGN KEY (fctorgid) REFERENCES public.org_personne(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tableau_personne_personneid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_personneid_fkey FOREIGN KEY (personneid) REFERENCES public.personne(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tableau_personne_tableauid_fkey; Type: FK CONSTRAINT; Schema: sco; Owner: ttt
--

ALTER TABLE ONLY tableau_personne
    ADD CONSTRAINT tableau_personne_tableauid_fkey FOREIGN KEY (tableauid) REFERENCES tableau(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

