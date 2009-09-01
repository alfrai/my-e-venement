SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

CREATE TABLE email (
    id integer NOT NULL,
    date timestamp with time zone DEFAULT now() NOT NULL,
    accountid bigint NOT NULL,
    "from" character varying(255) NOT NULL,
    "to" text NOT NULL,
    bcc text,
    subject text NOT NULL,
    content text NOT NULL,
    full_c text NOT NULL,
    full_h text NOT NULL,
    sent boolean DEFAULT false NOT NULL
);
COMMENT ON TABLE email IS 'where are recorded all emails sent by the "emailing" tool...';

CREATE SEQUENCE email_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

ALTER TABLE public.email_id_seq OWNER TO beta;
ALTER TABLE email ALTER COLUMN id SET DEFAULT nextval('email_id_seq'::regclass);
ALTER TABLE ONLY email
    ADD CONSTRAINT email_pkey PRIMARY KEY (id);
ALTER TABLE ONLY email
    ADD CONSTRAINT email_accountid_fkey FOREIGN KEY (accountid) REFERENCES account(id) ON UPDATE CASCADE ON DELETE SET NULL;
