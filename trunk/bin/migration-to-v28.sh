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


echo "Usage: bin/migration-to-v27.sh SFUSER [DB [USER [HOST [PORT]]]]"
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

db="$PGDATABASE"
[ -z "$db" ] && db=$USER

# recreation and data backup
dropdb $db && createdb $db && \
echo "GRANT ALL ON DATABASE $db TO $SFUSER" | psql && \
./symfony doctrine:build  --all --no-confirmation && \
cat data/sql/$db-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -a -d $db
cat config/doctrine/functions-pgsql.sql | psql && \
./symfony cc &> /dev/null
echo ""

[ ! -f apps/default/config/app.yml ] && cp apps/default/config/app.yml.template apps/default/config/app.yml

echo ""
echo "Be careful with DB errors. A table with an error is an empty table !... If necessary take back the DB backup and correct things by hand before retrying this migration script."
echo ""

# final data modifications
echo ""
echo "If you will get Symfony errors in the next few actions, it's normal, it is trying to add permissions that already exist in the DB"
echo "Permissions & groups for surveys..."
./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-srv.yml
echo "Permissions & groups for accessing backups..."
./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-backups.yml
psql $db <<EOF
EOF

echo ''
read -p "Do you want to refresh your Searchable data for Contacts & Organisms (recommanded, but it can take a while) ? [y/N] " refresh
if [ "$refresh" = 'y' ]; then
  psql $db <<EOF
DELETE FROM contact_index;
DELETE FROM organism_index;
EOF
  ./symfony e-venement:search-index Contact
  ./symfony e-venement:search-index Organism
fi

# final informations
echo ""
echo ""
echo "Don't forget to configure those extra features:"
echo "- e-venement Messaging Network: rm -rf web/liJappixPlugin; svn update; then run http[s]://[YOUR E-VENEMENT BASE ROOT]/liJappixPlugin"

echo ""
echo "Don't forget to inform your users about those evolutions"
