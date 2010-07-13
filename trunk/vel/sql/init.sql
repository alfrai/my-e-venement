SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

CREATE SCHEMA vel;


SET search_path = vel, billeterie, public, pg_catalog;

CREATE TABLE authentication (
    id SERIAL NOT NULL,
    ip character varying(255) NOT NULL,
    accountid bigint UNIQUE NOT NULL,
    salt character varying(255) NOT NULL DEFAULT ''
);

ALTER TABLE ONLY authentication
    ADD CONSTRAINT authentication_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;
