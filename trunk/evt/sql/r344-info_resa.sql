--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = billeterie, pg_catalog;

--
-- Name: info_resa; Type: VIEW; Schema: billeterie; Owner: beta
--

CREATE VIEW info_resa AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, manif.duree, evt.ages, evt.code, evt.creation, evt.modification, evt.catdesc, manif.id AS manifid, manif.date, CASE WHEN ((sm.manifid IS NULL) AND (space.id IS NULL)) THEN manif.jauge WHEN ((sm.manifid IS NULL) AND (space.id IS NOT NULL)) THEN 0 ELSE sm.jauge END AS jauge, space.id AS spaceid, manif.vel, manif.description AS manifdesc, site.id AS siteid, site.nom AS sitenom, site.ville, site.cp, manif.plnum, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM (reservation_pre resa LEFT JOIN transaction t ON ((t.id = resa.transaction))) WHERE (((((t.spaceid = space.id) OR ((t.spaceid IS NULL) AND (space.id IS NULL))) AND (resa.manifid = manif.id)) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (NOT (resa.transaction IN (SELECT preselled.transaction FROM preselled))))) AS commandes, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM (reservation_pre resa LEFT JOIN transaction t ON ((t.id = resa.transaction))) WHERE ((((t.spaceid = space.id) OR ((t.spaceid IS NULL) AND (space.id IS NULL))) AND (resa.manifid = manif.id)) AND (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AS resas, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM (reservation_pre resa LEFT JOIN transaction t ON ((t.id = resa.transaction))) WHERE (((((t.spaceid = space.id) OR ((t.spaceid IS NULL) AND (space.id IS NULL))) AND (resa.manifid = manif.id)) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (resa.transaction IN (SELECT preselled.transaction FROM preselled)))) AS preresas, evt.txtva AS deftva, manif.txtva, color.libelle AS colorname, color.color FROM evenement_categorie evt, site, (((manifestation manif LEFT JOIN color ON ((color.id = manif.colorid))) LEFT JOIN (SELECT NULL::integer AS id, NULL::character varying(255) AS name UNION SELECT space.id, space.name FROM space) space ON (true)) LEFT JOIN space_manifestation sm ON (((sm.manifid = manif.id) AND (sm.spaceid = space.id)))) WHERE ((evt.id = manif.evtid) AND (site.id = manif.siteid)) ORDER BY evt.catdesc, evt.nom, manif.date;


ALTER TABLE billeterie.info_resa OWNER TO beta;

--
-- PostgreSQL database dump complete
--

