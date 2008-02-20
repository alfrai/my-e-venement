--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: vel; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA vel;


--
-- Name: SCHEMA vel; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA vel IS 'module de vente en ligne';


SET search_path = vel, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: maniftosell; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE maniftosell (
    id integer NOT NULL,
    jauge integer DEFAULT 0 NOT NULL,
    highlight boolean DEFAULT false NOT NULL,
    selled integer DEFAULT 0 NOT NULL
);


--
-- Name: TABLE maniftosell; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON TABLE maniftosell IS 'les manifestations en vente en ligne, et la jauge max autorisée';


--
-- Name: COLUMN maniftosell.id; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN maniftosell.id IS 'manifestation.id';


--
-- Name: COLUMN maniftosell.jauge; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN maniftosell.jauge IS 'jauge max pour la vente en ligne';


--
-- Name: COLUMN maniftosell.highlight; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN maniftosell.highlight IS 'la manifestation est à mettre en avant ds la partie publique';


--
-- Name: COLUMN maniftosell.selled; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN maniftosell.selled IS 'places vendues en ligne';


--
-- Name: params; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE params (
    name character varying(255) NOT NULL,
    value character varying(255) NOT NULL
);


--
-- Name: TABLE params; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON TABLE params IS 'paramètres définis dans l''admin du module';


--
-- Name: COLUMN params.name; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN params.name IS 'Nom du paramètre';


--
-- Name: COLUMN params.value; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN params.value IS 'Valeur du paramètre';


--
-- Name: rights; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    "level" integer DEFAULT 0 NOT NULL
);


--
-- Name: TABLE rights; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON TABLE rights IS 'droits sur le module';


--
-- Name: COLUMN rights.id; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN rights.id IS 'account.id';


--
-- Name: COLUMN rights."level"; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN rights."level" IS 'niveau de droits';


--
-- Name: tariftosell; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE tariftosell (
    id integer NOT NULL,
    priority integer DEFAULT 0 NOT NULL
);


--
-- Name: TABLE tariftosell; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON TABLE tariftosell IS 'tarifs à utiliser dans le module de vente en ligne';


--
-- Name: COLUMN tariftosell.id; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN tariftosell.id IS 'tarif.id';


--
-- Name: COLUMN tariftosell.priority; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN tariftosell.priority IS 'priorité d''apparition';


--
-- Name: transaction; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE "transaction" (
    id bigint NOT NULL
);


--
-- Name: TABLE "transaction"; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON TABLE "transaction" IS 'permet de savoir si une transaction vient de la vente en ligne';


--
-- Name: COLUMN "transaction".id; Type: COMMENT; Schema: vel; Owner: -
--

COMMENT ON COLUMN "transaction".id IS 'transaction.id';


--
-- Name: params_pkey; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY params
    ADD CONSTRAINT params_pkey PRIMARY KEY (name);


--
-- Name: rights_pkey; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_pkey PRIMARY KEY (id);


--
-- Name: tarifs_pkey; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tariftosell
    ADD CONSTRAINT tarifs_pkey PRIMARY KEY (id);


--
-- Name: tosell_pkey; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY maniftosell
    ADD CONSTRAINT tosell_pkey PRIMARY KEY (id);


--
-- Name: transaction_pkey; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY "transaction"
    ADD CONSTRAINT transaction_pkey PRIMARY KEY (id);


--
-- Name: rights_id_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_id_fkey FOREIGN KEY (id) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tarifs_id_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY tariftosell
    ADD CONSTRAINT tarifs_id_fkey FOREIGN KEY (id) REFERENCES billeterie.tarif(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tosell_id_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY maniftosell
    ADD CONSTRAINT tosell_id_fkey FOREIGN KEY (id) REFERENCES billeterie.manifestation(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: transaction_id_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY "transaction"
    ADD CONSTRAINT transaction_id_fkey FOREIGN KEY (id) REFERENCES billeterie."transaction"(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

