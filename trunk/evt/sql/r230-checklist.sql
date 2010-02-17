--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = billeterie, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: checklist; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE checklist (
    id integer NOT NULL,
    evtid integer NOT NULL,
    checkpoint character varying(255) NOT NULL,
    description text,
    done timestamp with time zone,
    owner bigint NOT NULL,
    modifier bigint,
    doing timestamp with time zone
);


--
-- Name: TABLE checklist; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE checklist IS 'permet d''ajouter une liste de tâches à faire pour un événement donné';


--
-- Name: COLUMN checklist.checkpoint; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN checklist.checkpoint IS 'short comment';


--
-- Name: COLUMN checklist.description; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN checklist.description IS 'long comment (may be HTML content)';


--
-- Name: COLUMN checklist.done; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN checklist.done IS 'date of checked state';


--
-- Name: COLUMN checklist.owner; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN checklist.owner IS 'createur';


--
-- Name: COLUMN checklist.modifier; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN checklist.modifier IS 'derniere personne à avoir modifié le checkpoint';


--
-- Name: COLUMN checklist.doing; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN checklist.doing IS 'Someone is responsible of this checkpoint';


--
-- Name: checklist_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE checklist_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: checklist_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE checklist_id_seq OWNED BY checklist.id;


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE checklist ALTER COLUMN id SET DEFAULT nextval('checklist_id_seq'::regclass);


--
-- Name: checklist_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY checklist
    ADD CONSTRAINT checklist_pkey PRIMARY KEY (id);


--
-- Name: checklist_evtid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY checklist
    ADD CONSTRAINT checklist_evtid_fkey FOREIGN KEY (evtid) REFERENCES evenement(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: checklist_modifier_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY checklist
    ADD CONSTRAINT checklist_modifier_fkey FOREIGN KEY (modifier) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: checklist_owner_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY checklist
    ADD CONSTRAINT checklist_owner_fkey FOREIGN KEY (owner) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

