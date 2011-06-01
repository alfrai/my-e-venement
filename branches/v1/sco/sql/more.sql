SET search_path TO sco,billeterie,public;

ALTER TABLE tableau_personne
	ADD COLUMN confirmed boolean DEFAULT false NOT NULL;
COMMENT ON COLUMN tableau_personne.confirmed IS 'Indique si les interlocuteurs ont confirmé la réception de leur facture';

ALTER TABLE tableau_personne
	ADD COLUMN conftext text;
COMMENT ON COLUMN tableau_personne.conftext IS 'Permet de mettre un commentaire à propos de la confirmation de réception de la facture';

