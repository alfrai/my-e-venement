SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = billeterie, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

CREATE TABLE space (
    id integer NOT NULL,
    name character varying(255) NOT NULL
);
COMMENT ON TABLE space IS 'Defines spaces for ticketting system';
CREATE SEQUENCE space_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER SEQUENCE space_id_seq OWNED BY space.id;
SELECT pg_catalog.setval('space_id_seq', 1, true);
ALTER TABLE space ALTER COLUMN id SET DEFAULT nextval('space_id_seq'::regclass);
ALTER TABLE ONLY space
    ADD CONSTRAINT space_name_key UNIQUE (name);
ALTER TABLE ONLY space
    ADD CONSTRAINT space_pkey PRIMARY KEY (id);


-- 

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = billeterie, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

CREATE TABLE space_manifestation (
    id integer NOT NULL,
    spaceid integer NOT NULL,
    manifid integer NOT NULL,
    jauge integer DEFAULT 0 NOT NULL
);

COMMENT ON TABLE space_manifestation IS 'Defines specificities for manifestations in a defined space';
CREATE SEQUENCE space_manifestation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;
ALTER SEQUENCE space_manifestation_id_seq OWNED BY space_manifestation.id;
SELECT pg_catalog.setval('space_manifestation_id_seq', 1, false);
ALTER TABLE space_manifestation ALTER COLUMN id SET DEFAULT nextval('space_manifestation_id_seq'::regclass);
ALTER TABLE ONLY space_manifestation
    ADD CONSTRAINT space_manifestation_pkey PRIMARY KEY (id);
ALTER TABLE ONLY space_manifestation
    ADD CONSTRAINT space_manifestation_spaceid_key UNIQUE (spaceid, manifid);
ALTER TABLE ONLY space_manifestation
    ADD CONSTRAINT space_manifestation_manifid_fkey FOREIGN KEY (manifid) REFERENCES manifestation(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY space_manifestation
    ADD CONSTRAINT space_manifestation_spaceid_fkey FOREIGN KEY (spaceid) REFERENCES space(id) ON UPDATE CASCADE ON DELETE CASCADE;

DROP VIEW info_resa;
CREATE VIEW info_resa AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, manif.duree, evt.ages, evt.code, evt.creation, evt.modification, evt.catdesc, manif.id AS manifid, manif.date, CASE WHEN ((sm.manifid IS NULL) AND (space.id IS NULL)) THEN manif.jauge WHEN ((sm.manifid IS NULL) AND (space.id IS NOT NULL)) THEN 0 ELSE sm.jauge END AS jauge, space.id AS spaceid, manif.vel, manif.description AS manifdesc, site.id AS siteid, site.nom AS sitenom, site.ville, site.cp, manif.plnum, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (NOT (resa.transaction IN (SELECT preselled.transaction FROM preselled))))) AS commandes, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE ((resa.manifid = manif.id) AND (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AS resas, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (resa.transaction IN (SELECT preselled.transaction FROM preselled)))) AS preresas, evt.txtva AS deftva, manif.txtva, color.libelle AS colorname, color.color FROM evenement_categorie evt, site, (((manifestation manif LEFT JOIN color ON ((color.id = manif.colorid))) LEFT JOIN (SELECT NULL::integer AS id, NULL::character varying(255) AS name UNION SELECT space.id, space.name FROM space) space ON (true)) LEFT JOIN space_manifestation sm ON (((sm.manifid = manif.id) AND (sm.spaceid = space.id)))) WHERE ((evt.id = manif.evtid) AND (site.id = manif.siteid)) ORDER BY evt.catdesc, evt.nom, manif.date, spaceid DESC;

