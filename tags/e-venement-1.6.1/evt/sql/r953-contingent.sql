SET search_path TO billeterie,public;
CREATE OR REPLACE FUNCTION get_tarifid_contingeant(integer) RETURNS integer
    AS $$SELECT id AS result FROM tarif WHERE contingeant ORDER BY date DESC LIMIT 1;$$
    LANGUAGE sql STABLE STRICT;
