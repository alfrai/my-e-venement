--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = vel,billeterie,public,pg_catalog;

--
-- Name: authentication_id_seq; Type: SEQUENCE SET; Schema: vel; Owner: beta
--

SELECT pg_catalog.setval('authentication_id_seq', 1, true);


--
-- Data for Name: authentication; Type: TABLE DATA; Schema: vel; Owner: beta
--

INSERT INTO account(name,description,login,password,active,level)
     VALUES ('Vente en ligne','qs8613ih<è_azf','vel',md5('qs8613ih<è_azf'),true,5);

INSERT INTO rights(id,level)
     VALUES (41,5);

COPY vel.authentication (id, ip, accountid, salt) FROM stdin;
1	::1	41	aqolni21n_ç145q,
\.

--
-- PostgreSQL database dump complete
--

