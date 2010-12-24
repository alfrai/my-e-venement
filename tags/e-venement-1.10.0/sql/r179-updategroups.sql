delete from groupe_fonctions gf WHERE (groupid,fonctionid) IN ( SELECT groupid,fonctionid FROM groupe_fonctions t WHERE t.groupid = gf.groupid AND t.fonctionid = gf.fonctionid AND t.included) AND not gf.included;
update groupe_fonctions set included = true from groupe where creation > '2009-09-01'::date AND groupid = groupe.id AND not included;
update groupe_personnes set included = true from groupe where creation > '2009-09-01'::date AND groupid = groupe.id AND not included;

