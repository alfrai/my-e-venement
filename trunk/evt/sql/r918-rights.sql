--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = billeterie, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: rights; Type: TABLE; Schema: billeterie; Owner: beta; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    "level" integer DEFAULT 0 NOT NULL
);


ALTER TABLE billeterie.rights OWNER TO beta;

--
-- PostgreSQL database dump complete
--

