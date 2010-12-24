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

ALTER TABLE checklist ADD COLUMN isfile boolean NOT NULL DEFAULT false;
