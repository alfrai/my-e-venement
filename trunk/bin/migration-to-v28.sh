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
#psql <<EOF
#  DROP TABLE seating_plan;
#  ALTER TABLE transaction DROP COLUMN workspace_id;
#  ALTER TABLE transaction_version DROP COLUMN workspace_id;
#  UPDATE ticket SET numerotation = NULL WHERE trim(numerotation) = '';
#  ALTER TABLE group_deleted DROP COLUMN information;
#  
#  -- issue of duplicated unique index due to bad definition in the past
#  UPDATE entry_tickets et
#     SET quantity = (SELECT sum(quantity) FROM entry_tickets sub WHERE (et.entry_element_id, et.price_id, et.gauge_id) = (sub.entry_element_id, sub.price_id, sub.gauge_id) GROUP BY entry_element_id, price_id, gauge_id HAVING count(*) > 1)
#   WHERE (entry_element_id, price_id, gauge_id) IN (SELECT entry_element_id, price_id, gauge_id FROM entry_tickets GROUP BY entry_element_id, price_id, gauge_id HAVING count(*) > 1);
#  DELETE FROM entry_tickets
#   WHERE id IN (SELECT id FROM entry_tickets WHERE (entry_element_id, price_id, gauge_id) IN (SELECT entry_element_id, price_id, gauge_id FROM entry_tickets GROUP BY entry_element_id, price_id, gauge_id HAVING count(*) > 1))
#     AND id NOT IN (SELECT min(id) FROM entry_tickets WHERE (entry_element_id, price_id, gauge_id) IN (SELECT entry_element_id, price_id, gauge_id FROM entry_tickets GROUP BY entry_element_id, price_id, gauge_id HAVING count(*) > 1) GROUP BY entry_element_id, price_id, gauge_id, quantity);
#EOF

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
echo "Be careful with DB errors. A table with an error is an empty table !... If necessary take back the DB backup and correct things by hand before to retry this migration script."
echo ""

# final data modifications
echo ""
echo "Creating permissions for seated plans features"
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
