SET search_path TO billeterie;
ALTER TABLE site ADD COLUMN "jauge-min" integer;
ALTER TABLE site ADD COLUMN "jauge-max" integer;

