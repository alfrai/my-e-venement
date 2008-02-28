SET search_path = pro, pg_catalog;

--
-- Name: is_auto_paid(integer); Type: FUNCTION; Schema: pro; Owner: ttt
--

CREATE OR REPLACE FUNCTION is_auto_paid(integer) RETURNS boolean
    AS $_$
	SELECT billeterie.getprice($1,(SELECT value FROM params WHERE name = 'tarifpros' LIMIT 1)) = 0 AS RESULT;
$_$
    LANGUAGE sql STABLE STRICT;

DROP TABLE evtcat_topay CASCADE;
