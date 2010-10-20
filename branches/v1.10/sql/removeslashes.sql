update entite set nom = regexp_replace(nom,'\'\'','\'') where nom ILIKE '%\'\'%';
update personne set prenom = regexp_replace(prenom,'\'\'','\'') where prenom ILIKE '%\'\'%';
update entite set adresse = regexp_replace(adresse,'\'\'','\'') where adresse ILIKE '%\'\'%';
update entite set ville = regexp_replace(ville,'\'\'','\'') where ville ILIKE '%\'\'%';

update entite set nom = regexp_replace(nom,'\\\\\'','\'') WHERE nom ILIKE '%\\\\\'%';
update personne set prenom = regexp_replace(prenom,'\\\\\'','\'') WHERE prenom ILIKE '%\\\\\'%';
update entite set adresse = regexp_replace(adresse,'\\\\\'','\'') WHERE adresse ILIKE '%\\\\\'%';
update entite set ville = regexp_replace(ville,'\\\\\'','\'') WHERE ville ILIKE '%\\\\\'%';
