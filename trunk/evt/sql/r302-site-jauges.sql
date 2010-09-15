SET search_path TO billeterie,public;
ALTER TABLE site ADD COLUMN "jauge-min" integer;
ALTER TABLE site ADD COLUMN "jauge-max" integer;

DROP VIEW site_datas;
CREATE VIEW site_datas AS
  (( SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site."jauge-max", site."jauge-min", site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, organisme.id AS orgid, organisme.nom AS orgnom, organisme.ville AS orgville, personne.id AS persid, personne.titre AS perstitre, personne.nom AS persnom, personne.prenom AS persprenom, personne.protel AS perstel
     FROM site, organisme, personne_properso personne
       WHERE organisme.id = site.organisme AND personne.id = site.regisseur
       UNION 
        SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site."jauge-min", site."jauge-max", site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgville, personne.id AS persid, personne.titre AS perstitre, personne.nom AS persnom, personne.prenom AS persprenom, personne.protel AS perstel
           FROM site, personne_properso personne
             WHERE site.organisme IS NULL AND personne.id = site.regisseur)
             UNION 
              SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site."jauge-min", site."jauge-max", site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, organisme.id AS orgid, organisme.nom AS orgnom, organisme.ville AS orgville, NULL::unknown AS persid, NULL::unknown AS perstitre, NULL::unknown AS persnom, NULL::unknown AS persprenom, NULL::unknown AS perstel
                 FROM site, organisme
                   WHERE organisme.id = site.organisme AND site.regisseur IS NULL)
                   UNION 
                    SELECT site.id, site.nom, site.adresse, site.cp, site.ville, site.pays, site.regisseur, site.organisme, site."jauge-min", site."jauge-max", site.dimensions_salle, site.dimensions_scene, site.noir_possible, site.gradins, site.amperage, site.description, site.modification, site.creation, site.active, NULL::unknown AS orgid, NULL::unknown AS orgnom, NULL::unknown AS orgville, NULL::unknown AS persid, NULL::unknown AS perstitre, NULL::unknown AS persnom, NULL::unknown AS persprenom, NULL::unknown AS perstel
                       FROM site
                         WHERE site.organisme IS NULL AND site.regisseur IS NULL
                           ORDER BY 2, 5;
