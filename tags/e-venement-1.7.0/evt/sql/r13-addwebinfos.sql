SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = billeterie, pg_catalog;

ALTER TABLE evenement ADD COLUMN tarifweb NUMERIC(5,3);
ALTER TABLE evenement ADD COLUMN extradesc TEXT;
ALTER TABLE evenement ADD COLUMN extraspec TEXT;
ALTER TABLE evenement ADD COLUMN imageurl CHARACTER VARYING(255);

DROP VIEW info_resa;
DROP VIEW evenement_categorie;

CREATE VIEW evenement_categorie AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc,
           evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, evt.duree, evt.ages, evt.code, evt.creation,
           evt.modification, cat.libelle AS catdesc, cat.txtva, evt.metaevt,
           evt.tarifweb, evt.extradesc, evt.extraspec, evt.imageurl
    FROM evenement evt, evt_categorie cat
    WHERE ((evt.categorie = cat.id) AND (evt.categorie IS NOT NULL))
 UNION
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc,
           evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, evt.duree, evt.ages, evt.code, evt.creation,
           evt.modification, NULL::"unknown" AS catdesc, NULL::"unknown" AS txtva, evt.metaevt,
           evt.tarifweb, evt.extradesc, evt.extraspec, evt.imageurl
    FROM evenement evt
    WHERE (evt.categorie IS NULL)
 ORDER BY 18, 5;


ALTER TABLE billeterie.evenement_categorie OWNER TO ttt;

--
-- Name: VIEW evenement_categorie; Type: COMMENT; Schema: billeterie; Owner: ttt
--

COMMENT ON VIEW evenement_categorie IS 'Liste des organismes avec leur catégorie (qui est à NULL s''ils n''en ont pas)';


--
-- PostgreSQL database dump complete
--

CREATE VIEW info_resa AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie, evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, manif.duree, evt.ages, evt.code, evt.creation, evt.modification, evt.catdesc, manif.id AS manifid, manif.date, manif.jauge, manif.description AS manifdesc, site.id AS siteid, site.nom AS sitenom, site.ville, site.cp, manif.plnum, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (NOT (resa."transaction" IN (SELECT preselled."transaction" FROM preselled))))) AS commandes, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE ((resa.manifid = manif.id) AND (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AS resas, (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (resa."transaction" IN (SELECT preselled."transaction" FROM preselled)))) AS preresas, evt.txtva AS deftva, manif.txtva, colors.libelle AS colorname, colors.color FROM evenement_categorie evt, manifestation manif, site, colors WHERE (((evt.id = manif.evtid) AND (site.id = manif.siteid)) AND ((colors.id = manif.colorid) OR ((colors.id IS NULL) AND (manif.colorid IS NULL)))) ORDER BY evt.catdesc, evt.nom, manif.date;
COMMENT ON VIEW info_resa IS 'permet d''avoir d''un coup toutes les informations de réservation nécessaires';
