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
-- Name: vel; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA vel;


SET search_path = vel, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: authentication; Type: TABLE; Schema: vel; Owner: -; Tablespace: 
--

CREATE TABLE authentication (
    id integer NOT NULL,
    ip character varying(255) NOT NULL,
    accountid bigint NOT NULL,
    salt character varying(255) DEFAULT ''::character varying NOT NULL
);


--
-- Name: authentication_id_seq; Type: SEQUENCE; Schema: vel; Owner: -
--

CREATE SEQUENCE authentication_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: authentication_id_seq; Type: SEQUENCE OWNED BY; Schema: vel; Owner: -
--

ALTER SEQUENCE authentication_id_seq OWNED BY authentication.id;


--
-- Name: id; Type: DEFAULT; Schema: vel; Owner: -
--

ALTER TABLE authentication ALTER COLUMN id SET DEFAULT nextval('authentication_id_seq'::regclass);


--
-- Name: authentication_accountid_key; Type: CONSTRAINT; Schema: vel; Owner: -; Tablespace: 
--

ALTER TABLE ONLY authentication
    ADD CONSTRAINT authentication_accountid_key UNIQUE (ip,accountid);


--
-- Name: authentication_accountid_fkey; Type: FK CONSTRAINT; Schema: vel; Owner: -
--

ALTER TABLE ONLY authentication
    ADD CONSTRAINT authentication_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

