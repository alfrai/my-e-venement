#!/bin/bash

DB=$1

[ -z "$DB" ] && echo "Please, precise your DB as the first parameter" && exit 1
[ -z "$2" ] && echo We will only proceed to tests, if you want to update the DB, please precise "no test" as the last parameter

if [ -z "$2" ]; then
  echo "select t.id, t.cancelling, t2.duplicating, e.name, m.happens_at from ticket t2, ticket t left join manifestation m on m.id = t.manifestation_id left join event e on e.id = m.event_id where t.cancelling = t2.id and t2.duplicating is not null and t.cancelling is not null order by happens_at;" | psql $DB;
  exit 0;
fi

CPT=0
CONTINUE=true
while $CONTINUE; do
  TEST=`echo 'update ticket t set cancelling = t2.duplicating from ticket t2 where t.cancelling = t2.id and t2.duplicating is not null and t.cancelling is not null;' | psql $DB`
  [ "$TEST" = 'UPDATE 0' ] && CONTINUE=false
  let "CPT++"
  echo iteration $CPT
done
