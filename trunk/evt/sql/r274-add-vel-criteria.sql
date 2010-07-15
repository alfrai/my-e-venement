SET search_path TO billeterie;
ALTER TABLE tarif ADD COLUMN vel boolean DEFAULT false NOT NULL;
ALTER TABLE manifestation ADD COLUMN vel boolean DEFAULT false NOT NULL;

CREATE VIEW info_resa AS
    SELECT evt.id, evt.organisme1, evt.organisme2, evt.organisme3, evt.nom, evt.description, evt.categorie,
    evt.typedesc, evt.mscene, evt.mscene_lbl, evt.textede, evt.textede_lbl, manif.duree, evt.ages, evt.code,
    evt.creation, evt.modification, evt.catdesc, manif.id AS manifid, manif.date, manif.jauge, manif.vel,
    manif.description AS manifdesc, site.id AS siteid, site.nom AS sitenom, site.ville, site.cp, manif.plnum,
    (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AND (NOT (resa.transaction IN (SELECT preselled.transaction FROM preselled))))) AS commandes,
    (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE ((resa.manifid = manif.id) AND (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur WHERE (reservation_cur.canceled = false))))) AS resas,
    (SELECT sum((- (((resa.annul)::integer * 2) - 1))) AS sum FROM reservation_pre resa WHERE (((resa.manifid = manif.id) AND (NOT (resa.id IN (SELECT reservation_cur.resa_preid FROM reservation_cur
    WHERE (reservation_cur.canceled = false))))) AND (resa.transaction IN (SELECT preselled.transaction FROM preselled)))) AS preresas, evt.txtva AS deftva, manif.txtva, colors.libelle AS colorname, colors.color
    FROM evenement_categorie evt, manifestation manif, site, colors WHERE (((evt.id = manif.evtid) AND (site.id = manif.siteid)) AND ((colors.id = manif.colorid) OR ((colors.id IS NULL) AND (manif.colorid IS NULL))))
    ORDER BY evt.catdesc, evt.nom, manif.date;
