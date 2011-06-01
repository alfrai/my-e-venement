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
-- Name: cattarifs_elt; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE cattarifs_elt (
    id integer NOT NULL,
    rowid integer NOT NULL,
    lineid integer NOT NULL,
    tarifkey character varying(5) NOT NULL
);


--
-- Name: COLUMN cattarifs_elt.rowid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN cattarifs_elt.rowid IS 'cattarifs_row.id';


--
-- Name: COLUMN cattarifs_elt.lineid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN cattarifs_elt.lineid IS 'cattarifs_cell.id';


--
-- Name: COLUMN cattarifs_elt.tarifkey; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN cattarifs_elt.tarifkey IS 'tarif.key';


--
-- Name: cattarifs_elt_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE cattarifs_elt_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: cattarifs_elt_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE cattarifs_elt_id_seq OWNED BY cattarifs_elt.id;


--
-- Name: cattarifs_line; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE cattarifs_line (
    id integer NOT NULL,
    tableid integer NOT NULL,
    libelle character varying(255) NOT NULL
);


--
-- Name: COLUMN cattarifs_line.tableid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN cattarifs_line.tableid IS 'cattarifs_table.id';


--
-- Name: cattarifs_line_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE cattarifs_line_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: cattarifs_line_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE cattarifs_line_id_seq OWNED BY cattarifs_line.id;


--
-- Name: cattarifs_row; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE cattarifs_row (
    id integer NOT NULL,
    tableid integer NOT NULL,
    libelle character varying(255) NOT NULL
);


--
-- Name: TABLE cattarifs_row; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE cattarifs_row IS 'colonne';


--
-- Name: COLUMN cattarifs_row.tableid; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON COLUMN cattarifs_row.tableid IS 'cattarif_table.id';


--
-- Name: cattarifs_row_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE cattarifs_row_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: cattarifs_row_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE cattarifs_row_id_seq OWNED BY cattarifs_row.id;


--
-- Name: cattarifs_table; Type: TABLE; Schema: billeterie; Owner: -; Tablespace: 
--

CREATE TABLE cattarifs_table (
    id integer NOT NULL,
    libelle character varying(255) NOT NULL
);


--
-- Name: TABLE cattarifs_table; Type: COMMENT; Schema: billeterie; Owner: -
--

COMMENT ON TABLE cattarifs_table IS 'groupe de tarifs';


--
-- Name: cattarifs_table_id_seq; Type: SEQUENCE; Schema: billeterie; Owner: -
--

CREATE SEQUENCE cattarifs_table_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: cattarifs_table_id_seq; Type: SEQUENCE OWNED BY; Schema: billeterie; Owner: -
--

ALTER SEQUENCE cattarifs_table_id_seq OWNED BY cattarifs_table.id;


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE cattarifs_elt ALTER COLUMN id SET DEFAULT nextval('cattarifs_elt_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE cattarifs_line ALTER COLUMN id SET DEFAULT nextval('cattarifs_line_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE cattarifs_row ALTER COLUMN id SET DEFAULT nextval('cattarifs_row_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: billeterie; Owner: -
--

ALTER TABLE cattarifs_table ALTER COLUMN id SET DEFAULT nextval('cattarifs_table_id_seq'::regclass);


--
-- Name: cattarifs_elt_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_elt
    ADD CONSTRAINT cattarifs_elt_pkey PRIMARY KEY (id);


--
-- Name: cattarifs_elt_rowid_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_elt
    ADD CONSTRAINT cattarifs_elt_rowid_key UNIQUE (rowid, lineid, tarifkey);


--
-- Name: cattarifs_line_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_line
    ADD CONSTRAINT cattarifs_line_pkey PRIMARY KEY (id);


--
-- Name: cattarifs_line_tableid_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_line
    ADD CONSTRAINT cattarifs_line_tableid_key UNIQUE (tableid, libelle);


--
-- Name: cattarifs_row_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_row
    ADD CONSTRAINT cattarifs_row_pkey PRIMARY KEY (id);


--
-- Name: cattarifs_row_tableid_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_row
    ADD CONSTRAINT cattarifs_row_tableid_key UNIQUE (tableid, libelle);


--
-- Name: cattarifs_table_libelle_key; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_table
    ADD CONSTRAINT cattarifs_table_libelle_key UNIQUE (libelle);


--
-- Name: cattarifs_table_pkey; Type: CONSTRAINT; Schema: billeterie; Owner: -; Tablespace: 
--

ALTER TABLE ONLY cattarifs_table
    ADD CONSTRAINT cattarifs_table_pkey PRIMARY KEY (id);


--
-- Name: cattarifs_elt_lineid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY cattarifs_elt
    ADD CONSTRAINT cattarifs_elt_lineid_fkey FOREIGN KEY (lineid) REFERENCES cattarifs_line(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cattarifs_elt_rowid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY cattarifs_elt
    ADD CONSTRAINT cattarifs_elt_rowid_fkey FOREIGN KEY (rowid) REFERENCES cattarifs_row(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cattarifs_line_tableid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY cattarifs_line
    ADD CONSTRAINT cattarifs_line_tableid_fkey FOREIGN KEY (tableid) REFERENCES cattarifs_table(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cattarifs_row_tableid_fkey; Type: FK CONSTRAINT; Schema: billeterie; Owner: -
--

ALTER TABLE ONLY cattarifs_row
    ADD CONSTRAINT cattarifs_row_tableid_fkey FOREIGN KEY (tableid) REFERENCES cattarifs_table(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

