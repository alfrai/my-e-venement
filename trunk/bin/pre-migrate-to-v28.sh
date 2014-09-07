#!/bin/bash

#**********************************************************************************
#
#	    This file is part of e-venement.
# 
#    e-venement is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License.
# 
#    e-venement is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
# 
#    You should have received a copy of the GNU General Public License
#    along with e-venement; if not, write to the Free Software
#    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
# 
#    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
#    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
# 
#**********************************************************************************/

# preconditions
[ ! -d "data/sql" ] && echo "cd to your project's root directory please" && exit 3;

[ -z "$1" ] && echo "You must specify the DB user that is used by e-venement as the first parameter" && exit 1
SFUSER="$1"
[ -n "$2" ] && export PGDATABASE="$2"
[ -n "$3" ] && export PGUSER="$3"
[ -n "$4" ] && export PGHOST="$4"
[ -n "$5" ] && export PGPORT="$5"


echo "Usage: bin/pre-mirate-to-v28.sh SFUSER [DB [USER [HOST [PORT]]]]"
echo "Are you sure you want to continue with those parameters :"
echo "The e-venement's DB user: $SFUSER"
echo "Database: $PGDATABASE"
echo "User: $PGUSER"
echo "Host: $PGHOST"
echo "Port: $PGPORT"
echo ""
echo "To continue press ENTER"
echo "To cancel press CTRL+C NOW !!"
read

read -p "Do you want to reset your dump & patch your database for e-venement v2.7 ? [Y/n] " dump
if [ "$dump" != "n" ]; then

name="$PGDATABASE"
[ -z "$name" ] && name=db

echo "DUMPING DB..."
pg_dump -Fc > data/sql/$name-`date +%Y%m%d`.before.pgdump && echo "DB pre dumped"

## preliminary modifications & backup
psql <<EOF
  CREATE TABLE event_translation (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    short_name character varying(127),
    description text,
    extradesc text,
    extraspec text,
    lang character(2) NOT NULL
  );
  INSERT INTO event_translation (SELECT id, name, short_name, description, extradesc, extraspec, 'fr' FROM event);
  ALTER TABLE event DROP COLUMN name;
  ALTER TABLE event DROP COLUMN short_name;
  ALTER TABLE event DROP COLUMN description;
  ALTER TABLE event DROP COLUMN extradesc;
  ALTER TABLE event DROP COLUMN extraspec;
  
  ALTER TABLE event_version ADD COLUMN lang character(2) NOT NULL DEFAULT 'fr';

  ALTER TABLE ticket ADD COLUMN seat_id integer;
  UPDATE ticket tck
  SET seat_id = s.id
  FROM gauge g
  LEFT JOIN manifestation m ON m.id = g.manifestation_id
  LEFT JOIN location l ON l.id = m.location_id
  LEFT JOIN seated_plan sp ON sp.location_id = l.id
  LEFT JOIN seated_plan_workspace spw ON spw.workspace_id = g.workspace_id AND spw.seated_plan_id = sp.id
  LEFT JOIN seat s ON s.seated_plan_id = sp.id
  WHERE g.id = tck.gauge_id
    AND s.name = tck.numerotation
    AND tck.numerotation IS NOT NULL
    AND spw.workspace_id IS NOT NULL;
  ALTER TABLE ticket DROP COLUMN numerotation;

  ALTER TABLE ticket_version ADD COLUMN seat_id integer;
  UPDATE ticket_version tck
  SET seat_id = s.id
  FROM gauge g
  LEFT JOIN manifestation m ON m.id = g.manifestation_id
  LEFT JOIN location l ON l.id = m.location_id
  LEFT JOIN seated_plan sp ON sp.location_id = l.id
  LEFT JOIN seated_plan_workspace spw ON spw.workspace_id = g.workspace_id AND spw.seated_plan_id = sp.id
  LEFT JOIN seat s ON s.seated_plan_id = sp.id
  WHERE g.id = tck.gauge_id
    AND s.name = tck.numerotation
    AND tck.numerotation IS NOT NULL
    AND spw.workspace_id IS NOT NULL;
  ALTER TABLE ticket_version DROP COLUMN numerotation;
EOF

echo "DUMPING DB..."
pg_dump -Fc > data/sql/$name-`date +%Y%m%d`.pgdump && echo "DB dumped"

fi #end of "allow dumps" condition

echo ""
read -p "Do you want to reset properly your lib/model, lib/form & lib/filter files using SVN ? [y/N] " reset
if [ "$reset" = 'y' ]; then
  rm -rf lib/*/doctrine/
  svn update
fi

echo ""
echo ""
db="$PGDATABASE"
[ -z "$db" ] && db=$USER

# recreation and data backup
if dropdb $db && createdb $db
then
  echo "You can now continue throught the migration executing:"
  echo ""
  echo "1. ./symfony doctrine:drop-db --no-confirmation && ./symfony doctrine:build-db && ./symfony doctrine:build-model && ./symfony doctrine:build-forms && ./symfony doctrine:build-filters && ./symfony doctrine:build-sql && ./symfony doctrine:insert-sql && echo 'Now you can execute:' && echo '  bin/migrate-to-v28.sh $1 $2 $3 $4 $5'"
  echo "2. bin/migrate-to-v28.sh"
else
  echo "An error occurred, check it out"
fi
