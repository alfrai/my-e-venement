SET search_path TO billeterie,public;

ALTER TABLE manifestation
	ADD COLUMN updated timestamp with time zone DEFAULT now() NOT NULL;
ALTER TABLE manifestation
	ADD COLUMN created timestamp with time zone DEFAULT now() NOT NULL;
COMMENT ON COLUMN manifestation.updated IS 'date de dernier accès en écriture';
COMMENT ON COLUMN manifestation.created IS 'date de création';

CREATE OR REPLACE FUNCTION billeterie.manif_update()
  RETURNS "trigger" AS
  '
   BEGIN
      NEW.updated = NOW();
      RETURN NEW;
   END;
  '
  LANGUAGE 'plpgsql' VOLATILE;

CREATE TRIGGER manifestation_trigger
  BEFORE UPDATE
  ON billeterie.manifestation
  FOR EACH ROW
  EXECUTE PROCEDURE manif_update();
