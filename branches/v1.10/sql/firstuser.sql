SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Data for Name: account; Type: TABLE DATA; Schema: public;
--

INSERT	INTO account(name, login, password, level, email)
	VALUES ('Admin', 'admin', md5('pass'), 20, '');
