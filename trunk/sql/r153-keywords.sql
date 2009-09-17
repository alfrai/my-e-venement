ALTER TABLE personne ADD COLUMN description TEXT DEFAULT NULL;

BEGIN TRANSACTION;

DROP VIEW personne_extractor;
DROP VIEW personne_telephone;
DROP VIEW billeterie.waitingdepots;
DROP VIEW billeterie.site_datas;
DROP VIEW personne_properso;

CREATE VIEW personne_properso AS
((( SELECT DISTINCT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id AS orgid, organisme.nom AS orgnom, organisme.categorie AS orgcat, organisme.adresse AS orgadr, organisme.cp AS orgcp, organisme.ville AS orgville, organisme.pays AS orgpays, organisme.email AS orgemail, organisme.url AS orgurl, organisme.description AS orgdesc, org_personne.service, org_personne.id AS fctorgid, fonction.id AS fctid, fonction.libelle AS fcttype, org_personne.fonction AS fctdesc, org_personne.email AS proemail, org_personne.telephone AS protel, organisme.catdesc AS orgcatdesc, personne.description
   FROM organisme_categorie organisme, personne, org_personne, fonction
     WHERE personne.id = org_personne.personneid AND organisme.id = org_personne.organismeid AND fonction.id = org_personne.type AND org_personne.type IS NOT NULL
       ORDER BY personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id, organisme.nom, organisme.categorie, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.url, organisme.description, org_personne.service, org_personne.id, fonction.id, fonction.libelle, org_personne.fonction, org_personne.email, org_personne.telephone, organisme.catdesc, personne.description)
       UNION 
       ( SELECT DISTINCT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id AS orgid, organisme.nom AS orgnom, organisme.categorie AS orgcat, organisme.adresse AS orgadr, organisme.cp AS orgcp, organisme.ville AS orgville, organisme.pays AS orgpays, organisme.email AS orgemail, organisme.url AS orgurl, organisme.description AS orgdesc, org_personne.service, org_personne.id AS fctorgid, NULL::integer AS fctid, NULL::text AS fcttype, org_personne.fonction AS fctdesc, org_personne.email AS proemail, org_personne.telephone AS protel, organisme.catdesc AS orgcatdesc, personne.description
          FROM organisme_categorie organisme, personne, org_personne
            WHERE personne.id = org_personne.personneid AND organisme.id = org_personne.organismeid AND org_personne.type IS NULL
              ORDER BY personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, organisme.id, organisme.nom, organisme.categorie, organisme.adresse, organisme.cp, organisme.ville, organisme.pays, organisme.email, organisme.url, organisme.description, org_personne.service, org_personne.id, NULL::integer, NULL::text, org_personne.fonction, org_personne.email, org_personne.telephone, organisme.catdesc, personne.description))
              UNION 
               SELECT personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgcat, NULL::unknown AS orgadr, NULL::unknown AS orgcp, NULL::unknown AS orgville, NULL::unknown AS orgpays, NULL::unknown AS orgemail, NULL::unknown AS orgurl, NULL::unknown AS orgdesc, NULL::unknown AS service, NULL::unknown AS fctorgid, NULL::unknown AS fctid, NULL::unknown AS fcttype, NULL::unknown AS fctdesc, NULL::unknown AS proemail, NULL::unknown AS protel, NULL::unknown AS orgcatdesc, description AS description
                  FROM personne)
                  UNION 
                   SELECT NULL::unknown AS id, NULL::unknown AS nom, NULL::unknown AS creation, NULL::unknown AS modification, NULL::unknown AS adresse, NULL::unknown AS cp, NULL::unknown AS ville, NULL::unknown AS pays, NULL::unknown AS email, NULL::unknown AS npai, NULL::unknown AS active, NULL::unknown AS prenom, NULL::unknown AS titre, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgcat, NULL::unknown AS orgadr, NULL::unknown AS orgcp, NULL::unknown AS orgville, NULL::unknown AS orgpays, NULL::unknown AS orgemail, NULL::unknown AS orgurl, NULL::unknown AS orgdesc, NULL::unknown AS service, NULL::unknown AS fctorgid, NULL::unknown AS fctid, NULL::unknown AS fcttype, NULL::unknown AS fctdesc, NULL::unknown AS proemail, NULL::unknown AS protel, NULL::unknown AS orgcatdesc, NULL::unknown AS description
                     ORDER BY 2, 12, 15, 27, 28, 24;

-- SELECT p.id, p.nom, p.creation, p.modification, p.adresse, p.cp, p.ville, p.pays, p.email, p.npai, p.active, p.prenom, p.titre, o.id AS orgid, o.nom AS orgnom, o.categorie AS orgcat, o.adresse AS orgadr, o.cp AS orgcp, o.ville AS orgville, o.pays AS orgpays, o.email AS orgemail, o.url AS orgurl, o.description AS orgdesc, op.service, op.id AS fctorgid, op.type AS fctid, f.libelle AS fcttype, op.fonction AS fctdesc, op.email AS proemail, op.telephone AS protel, oc.libelle AS orgcatdesc, p.description
--   FROM personne p
--   LEFT JOIN ( SELECT org_personne.id, org_personne.personneid, org_personne.organismeid, org_personne.fonction, org_personne.email, org_personne.service, org_personne.type, org_personne.telephone, org_personne.description
--           FROM org_personne
--UNION 
--         SELECT NULL::unknown AS id, NULL::unknown AS personneid, NULL::unknown AS organismeid, NULL::unknown AS fonction, NULL::unknown AS email, NULL::unknown AS service, NULL::unknown AS type, NULL::unknown AS telephone, NULL::unknown AS description) op ON op.personneid = p.id OR op.personneid IS NULL
--   LEFT JOIN organisme o ON o.id = op.organismeid
--   LEFT JOIN fonction f ON f.id = op.type
--   LEFT JOIN org_categorie oc ON oc.id = o.categorie
--  ORDER BY 2, 12, 15, 27, 28, 24;

CREATE VIEW personne_telephone AS
 SELECT organisme.id, NULL::unknown AS type, NULL::unknown AS numero
   FROM organisme
  WHERE NOT (organisme.id IN ( SELECT telephone_organisme.entiteid
           FROM telephone_organisme))
UNION 
 SELECT organisme.id, telephone.type, telephone.numero
   FROM organisme, telephone_organisme telephone
  WHERE organisme.id = telephone.entiteid
  ORDER BY 1, 2, 3;


CREATE VIEW personne_extractor AS
 SELECT personne.id, personne.nom, personne.prenom, personne.titre, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.creation, personne.modification, ( SELECT personne_telephone.numero
           FROM personne_telephone
          WHERE personne.id = personne_telephone.id
         LIMIT 1) AS telnum, ( SELECT personne_telephone.type
           FROM personne_telephone
          WHERE personne.id = personne_telephone.id
         LIMIT 1) AS teltype, personne.orgid, personne.orgnom, personne.orgcatdesc AS orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, ( SELECT organisme_telephone.numero
           FROM organisme_telephone
          WHERE personne.orgid = organisme_telephone.id
         LIMIT 1) AS orgtelnum, ( SELECT organisme_telephone.type
           FROM organisme_telephone
          WHERE personne.orgid = organisme_telephone.id
         LIMIT 1) AS orgteltype
   FROM personne_properso personne;

SET search_path TO billeterie, public;

CREATE VIEW billeterie.waitingdepots AS
 SELECT DISTINCT contingeant.transaction, contingeant.closed, contingeant.date, personne.id, personne.nom, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.prenom, personne.titre, personne.orgid, personne.orgnom, personne.orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, personne.orgcatdesc, account.name, ( SELECT count(*) AS count
           FROM reservation_pre
          WHERE reservation_pre.transaction = transaction.id AND NOT reservation_pre.annul) AS total, ( SELECT count(*) AS count
           FROM reservation_pre, tarif
          WHERE reservation_pre.transaction = transaction.id AND reservation_pre.tarifid = tarif.id AND tarif.contingeant AND NOT reservation_pre.annul) AS cont, ( SELECT sum(masstickets.nb) AS nb
           FROM masstickets
          WHERE masstickets.transaction = transaction.id) AS masstick
   FROM personne_properso personne, billeterie.contingeant, account, billeterie.transaction
  WHERE (personne.fctorgid = contingeant.fctorgid OR personne.fctorgid IS NULL AND contingeant.fctorgid IS NULL) AND personne.id = contingeant.personneid AND account.id = contingeant.accountid AND transaction.id = contingeant.transaction AND (( SELECT count(*) AS count
           FROM reservation_pre
          WHERE reservation_pre.transaction = transaction.id AND NOT reservation_pre.annul)) > 0
  ORDER BY contingeant.transaction DESC, personne.nom, personne.prenom, personne.orgnom, personne.id, personne.creation, personne.modification, personne.adresse, personne.cp, personne.ville, personne.pays, personne.email, personne.npai, personne.active, personne.titre, personne.orgid, personne.orgcat, personne.orgadr, personne.orgcp, personne.orgville, personne.orgpays, personne.orgemail, personne.orgurl, personne.orgdesc, personne.service, personne.fctorgid, personne.fctid, personne.fcttype, personne.fctdesc, personne.proemail, personne.protel, personne.orgcatdesc, account.name, ( SELECT count(*) AS count
           FROM reservation_pre
          WHERE reservation_pre.transaction = transaction.id AND NOT reservation_pre.annul), ( SELECT count(*) AS count
           FROM reservation_pre, tarif
          WHERE reservation_pre.transaction = transaction.id AND reservation_pre.tarifid = tarif.id AND tarif.contingeant AND NOT reservation_pre.annul), ( SELECT sum(masstickets.nb) AS nb
           FROM masstickets
          WHERE masstickets.transaction = transaction.id), contingeant.closed, contingeant.date;

CREATE VIEW billeterie.site_datas AS
(( SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, organisme.id AS orgid, organisme.nom AS orgnom, organisme.ville AS orgville, personne.id AS persid, personne.titre AS perstitre, personne.nom AS persnom, personne.prenom AS persprenom, personne.protel AS perstel
   FROM billeterie.site, organisme, personne_properso personne
  WHERE organisme.id = site.organisme AND personne.id = site.regisseur
UNION 
 SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgville, personne.id AS persid, personne.titre AS perstitre, personne.nom AS persnom, personne.prenom AS persprenom, personne.protel AS perstel
   FROM billeterie.site, personne_properso personne
  WHERE site.organisme IS NULL AND personne.id = site.regisseur)
UNION 
 SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, organisme.id AS orgid, organisme.nom AS orgnom, organisme.ville AS orgville, NULL::unknown AS persid, NULL::unknown AS perstitre, NULL::unknown AS persnom, NULL::unknown AS persprenom, NULL::unknown AS perstel
   FROM billeterie.site, organisme
  WHERE organisme.id = site.organisme AND site.regisseur IS NULL)
UNION 
 SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgville, NULL::unknown AS persid, NULL::unknown AS perstitre, NULL::unknown AS persnom, NULL::unknown AS persprenom, NULL::unknown AS perstel
   FROM billeterie.site
  WHERE site.organisme IS NULL AND site.regisseur IS NULL
  ORDER BY 2, 5;

END TRANSACTION;

