SET search_path TO billeterie;
ALTER TABLE reservation_pre ALTER COLUMN reduc SET DEFAULT 0;
UPDATE reservation_pre SET reduc = 0 WHERE reduc IS NULL;
