--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = billeterie, pg_catalog;

--
-- Name: toomanyannul(integer, boolean); Type: FUNCTION; Schema: billeterie; Owner: beta
--

CREATE OR REPLACE FUNCTION toomanyannul(integer, boolean) RETURNS boolean
    AS $_$
DECLARE
  manif ALIAS FOR $1;
  annul ALIAS FOR $2;
  result boolean;
BEGIN
result := true;
IF ( annul )
THEN
  SELECT INTO result sum(nb) >= 0
  FROM tickets2print_bymanif(manif)
  WHERE canceled = false
    AND printed = true;
END IF;
RETURN result;
END;$_$
    LANGUAGE plpgsql;
