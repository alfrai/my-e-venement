--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: child; Type: TABLE; Schema: public; Owner: beta; Tablespace: 
--

CREATE TABLE child (
    id integer NOT NULL,
    personneid integer NOT NULL,
    birth integer NOT NULL,
    name text
);


--
-- Name: TABLE child; Type: COMMENT; Schema: public; Owner: beta
--

COMMENT ON TABLE child IS 'Permet de définir l''âge des enfants d''un contact';


--
-- Name: COLUMN child.personneid; Type: COMMENT; Schema: public; Owner: beta
--

COMMENT ON COLUMN child.personneid IS 'personne.id';


--
-- Name: COLUMN child.birth; Type: COMMENT; Schema: public; Owner: beta
--

COMMENT ON COLUMN child.birth IS 'year of birth';
COMMENT ON COLUMN child.name IS 'child''s name';


--
-- Name: child_id_seq; Type: SEQUENCE; Schema: public; Owner: beta
--

CREATE SEQUENCE child_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.child_id_seq OWNER TO beta;

--
-- Name: child_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: beta
--

ALTER SEQUENCE child_id_seq OWNED BY child.id;


--
-- Name: child_id_seq; Type: SEQUENCE SET; Schema: public; Owner: beta
--

SELECT pg_catalog.setval('child_id_seq', 1, false);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: beta
--

ALTER TABLE child ALTER COLUMN id SET DEFAULT nextval('child_id_seq'::regclass);


--
-- Data for Name: child; Type: TABLE DATA; Schema: public; Owner: beta
--

COPY child (id, personneid, birth) FROM stdin;
\.


--
-- Name: child_pkey; Type: CONSTRAINT; Schema: public; Owner: beta; Tablespace: 
--

ALTER TABLE ONLY child
    ADD CONSTRAINT child_pkey PRIMARY KEY (id);


--
-- Name: child_personneid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: beta
--

ALTER TABLE ONLY child
    ADD CONSTRAINT child_personneid_fkey FOREIGN KEY (personneid) REFERENCES personne(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

ALTER TABLE groupe_andreq
	ADD COLUMN childmax integer;
ALTER TABLE groupe_andreq
	ADD COLUMN childmin integer;

COMMENT ON COLUMN groupe_andreq.childmax IS 'date("Y") - childmax >= child.birth';
COMMENT ON COLUMN groupe_andreq.childmin IS 'date("Y") - childmin <= child.birth';
