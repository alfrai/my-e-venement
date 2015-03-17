CREATE OR REPLACE FUNCTION sum_aggreg (p integer, n boolean) RETURNS integer AS $$
  SELECT $2::integer + $1 AS result;
$$ LANGUAGE sql IMMUTABLE;

DROP AGGREGATE sum(boolean);

CREATE AGGREGATE sum (
  basetype = boolean,
  sfunc = sum_aggreg,
  stype = integer,
  initcond = 0
);

