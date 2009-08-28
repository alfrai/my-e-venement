--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: color_id_seq; Type: SEQUENCE SET; Schema: public; Owner: beta
--

SELECT pg_catalog.setval('color_id_seq', 8, true);


--
-- Data for Name: color; Type: TABLE DATA; Schema: public; Owner: beta
--

COPY color (id, libelle, color) FROM stdin;
6	bleu	d0ecff
5	jaune	fffaa1
7	rose	ffd7cb
8	vert	cfffc0
\.


--
-- PostgreSQL database dump complete
--

