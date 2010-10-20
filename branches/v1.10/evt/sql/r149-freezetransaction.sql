SET search_path TO billeterie;
ALTER TABLE transaction ADD COLUMN blocked boolean DEFAULT false;

