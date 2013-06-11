#!/bin/bash

DB=$1

[ -z "$DB" ] && echo "Please, precise your DB as the first parameter" && exit 1
[ -z "$2" ] && echo 'We will only proceed to tests, if you want to update the DB, please precise "notest" as the last parameter'

  echo "Problems with duplicatas:"
  echo "select t.id, t.cancelling, t2.duplicating, e.name, m.happens_at from ticket t2, ticket t left join manifestation m on m.id = t.manifestation_id left join event e on e.id = m.event_id where t.cancelling = t2.id and t2.duplicating is not null and t.cancelling is not null order by happens_at;" | psql $DB;
  echo ""
  echo "Problem with multi-cancelling:"
  echo "select cancelling as ticket_id, count(id) AS nb, min(id) AS cancelling_min_id, max(id) AS cancelling_max_id, max(updated_at) AS last_change from ticket t where cancelling is not null and duplicating is null and cancelling in (select cancelling from ticket where t.id != id and cancelling is not null and duplicating is null) group by cancelling;" | psql $DB
  [ "$2" != 'notest' ] && exit 0;

CPT=0
CONTINUE=true
while $CONTINUE; do
  TEST=`echo 'update ticket t set cancelling = t2.duplicating from ticket t2 where t.cancelling = t2.id and t2.duplicating is not null and t.cancelling is not null;' | psql $DB`
  [ "$TEST" = 'UPDATE 0' ] && CONTINUE=false
  let "CPT++"
  echo iteration $CPT
done

CPT=0
CONTINUE=true
while $CONTINUE; do
  TEST=`echo "UPDATE ticket t
        SET duplicating = (select max(id) AS cancelling_max_id from ticket t2 where cancelling = t.cancelling and cancelling is not null and duplicating is null)
        WHERE id IN (select max(id) AS cancelling_max_id from ticket t3 where cancelling is not null and duplicating is null and cancelling in (select cancelling from ticket where t.id != id and cancelling is not null and duplicating is null) group by cancelling);" | psql $DB`
  [ "$TEST" = 'UPDATE 0' ] && CONTINUE=false
  let "CPT++"
  echo iteration $CPT
done
