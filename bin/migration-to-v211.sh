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
#    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
#    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
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


echo "Usage: bin/migration-to-v210.sh SFUSER [DB [USER [HOST [PORT]]]]"
echo "Are you sure you want to continue with those parameters :"
echo "The e-venement's DB user: $SFUSER"
echo "Database: $PGDATABASE"
echo "User: $PGUSER"
echo "Host: $PGHOST"
echo "Port: $PGPORT"
echo ""
echo "- Please check config/autoload.inc.php.template and complete config/autoload.inc.php in that way..."
echo "- Have you upgraded your submodules individually before running this migration script? If no, do it first!"
echo ""
echo "To continue press ENTER"
echo "To cancel press CTRL+C NOW !!"
read

# Read Password
if [ -n "$PGHOST" ]  && [ -z "$PGPASSWORD" ]; then
    echo -n Password:
    read -s password
    # Run Command
    export PGPASSWORD=$password
fi

# Checking data
i=0; for elt in `echo 'SELECT count(*) FROM ticket WHERE (printed_at IS NOT NULL OR integrated_at IS NOT NULL);' | psql`
do let "i++"; [ $i -eq 3 ] && NBT=$elt; done
i=0; for elt in `echo 'SELECT count(*) FROM ticket WHERE (printed_at IS NOT NULL OR integrated_at IS NOT NULL) AND seat_id IS NOT NULL;' | psql 2> /dev/null`
do let "i++";  [ $i -eq 3 ] && NBP=$elt; done
if [ $i -eq 0 ]
then for elt in `echo "SELECT count(*) FROM ticket WHERE (printed_at IS NOT NULL OR integrated_at IS NOT NULL) AND numerotation IS NOT NULL AND numerotation != '';" | psql`
  do let "i++"; [ $i -eq 3 ] && NBP=$elt; done
fi
i=0; for elt in `echo 'SELECT count(*) FROM transaction;' | psql 2> /dev/null`
do let "i++";  [ $i -eq 3 ] && NBTR=$elt; done

read -p "Do you want to pull all your git submodules ? [Y/n] " subm
if [ "$subm" != "n" ]; then
  ./bin/update-submodules.sh
fi

echo ""
read -p "Do you want to reset your dump & patch your database for e-venement v2.11 ? [Y/n] " dump
if [ "$dump" != "n" ]; then

name="$PGDATABASE"
[ -z "$name" ] && name=db

## Deleting unwanted column
echo 'ALTER TABLE price DROP COLUMN IF EXISTS target;' | psql $PGDATABASE

## preliminary modifications & backup
echo "DUMPING DB..."
[ -f  data/sql/$name-`date +%Y%m%d`.before.pgdump ] && \
mv data/sql/$name-`date +%Y%m%d`.before.pgdump data/sql/$name-`date +%Y%m%d%H%M%s`.before.pgdump
pg_dump -Fc > data/sql/$name-`date +%Y%m%d`.before.pgdump && echo "DB pre dumped"

echo 'DELETE FROM cache;' | psql
## DO STUFF IN THE DB HERE

## Remove the content before changing the structure of the table
cities=`echo "SELECT count(*) FROM information_schema.columns WHERE table_name = 'postalcode' AND column_name = 'insee';" | psql $PGDATABASE | grep '[0-9]' | grep -v \(`
if [ $cities -eq 0 ]
then
echo "Removing cities to add INSEE code."
echo 'DELETE FROM postalcode' | psql $PGDATABASE
fi

psql <<EOF
EOF
echo "DUMPING DB..."
pg_dump -Fc > data/sql/$name-`date +%Y%m%d`.pgdump && echo "DB dumped"

fi #end of "allow dumps" condition

echo ""
echo ""
echo "RESETTING YOUR BASE DOCTRINE FILES"
rm -f lib/*/doctrine/base/*
rm -f lib/*/doctrine/*/base/*

echo ""
echo ""
db="$PGDATABASE"
[ -z "$db" ] && db=$USER

echo "Resetting the DB"
echo ""
# recreation and data backup
# those rm -rf cache/* are hacks to avoid cache related segfaults...
#dropdb $db;
#createdb $db
echo "DROP SCHEMA IF EXISTS public CASCADE;" | psql $PGDATABASE
echo "CREATE SCHEMA public;" | psql $PGDATABASE
echo "GRANT ALL ON SCHEMA public TO $SFUSER;" | psql $PGDATABASE

last=$?
./symfony cc
[ $last -eq 0 ] && ./symfony doctrine:drop-db --no-confirmation && ./symfony doctrine:build-db
last=$?
./symfony cc
[ $last -eq 0 ] && ./symfony doctrine:build --model
last=$?
./symfony cc
[ $last -eq 0 ] && ./symfony doctrine:build --forms
last=$?
./symfony cc
[ $last -eq 0 ] && ./symfony doctrine:build --filters
last=$?
./symfony cc
[ $last -eq 0 ] && ./symfony doctrine:build --sql
last=$?
./symfony cc
[ $last -eq 0 ] && ./symfony doctrine:insert-sql
if [ ! $? -eq 0 ]
then
  echo "";
  echo "  ... failed."
  exit 255
fi

echo "";
echo "  ... done."
echo "Re-injecting your data..."
cat data/sql/$db-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -a -d $db
#cat data/sql/$db-`date +%Y%m%d`.pgdump | pg_restore -Fc -a -d $db
if [ $? -eq 0 ]
then
  echo "... done."
else
  echo "... failed."
fi

echo ""
echo "Creating SQL needed functions ..."
cat config/doctrine/functions-pgsql.sql | psql
echo "... done."

[ ! -f apps/default/config/app.yml ] && cp apps/default/config/app.yml.template apps/default/config/app.yml

echo ""
echo "Be careful with DB errors. A table with an error is an empty table !... If necessary take back the DB backup and correct things by hand before retrying this migration script."
echo ""

echo ''
read -p "Do you want to refresh your Searchable data for Contacts & Organisms (recommanded, but it can take a while) ? [y/N] " refresh
if [ "$refresh" == 'y' ]; then
  psql $db <<EOF
DELETE FROM contact_index;
DELETE FROM organism_index;
DELETE FROM event_index;
EOF
  ./symfony e-venement:search-index Contact
  ./symfony e-venement:search-index Organism
  ./symfony e-venement:search-index Event
fi

# final data modifications

echo ''
read -p "Do you want to fix price ranking ? [Y/n]" price
if [ "$price" != 'n' ]
then
  psql $db <<EOF
  -- Create an index on the rank column
      CREATE INDEX rank_index ON price(rank);
  -- Sort the table with the index
      CLUSTER price USING rank_index;
  -- Create a sequence for the rank column
      CREATE SEQUENCE seqPriceRank START 1;
  -- Update rank column
      UPDATE price p
      SET rank = n.irank
      FROM (
        SELECT id, nextval('seqPriceRank') AS irank
        FROM price
      ) n
      WHERE p.id = n.id;
  -- Delete sequence
      DROP SEQUENCE seqPriceRank;
  -- Delete index
      DROP INDEX rank_index;
EOF
fi

echo ''
read -p "Do you want to move price ranking into the new table ? [Y/n]" rank
if [ "$rank" != 'n' ]
then
  # include parse_yaml function
  . $(dirname "$0")/parse_yml.sh
  # read yaml file
  eval $(parse_yaml $(dirname "$0")/../config/project.yml "project_")
  
  psql $db <<EOF
  DELETE FROM price_rank
  WHERE domain = '$project_all_internals_users_domain';
EOF
  
  psql $db <<EOF
  -- Move the rank column into a new table price_rank
      INSERT INTO price_rank (price_id, domain, rank)
      (
        SELECT id, '$project_all_internals_users_domain', rank
        FROM price
      );
EOF
fi

echo ''
echo "Changing (or not) file permissions for the e-venement Messaging Network ..."
chmod -R 777 web/liJappixPlugin/store web/liJappixPlugin/tmp web/liJappixPlugin/log &> /dev/null
echo "... done."

echo ""
read -p "Do you want to update your french geographical data, with departements & regions? [Y/n] " geo
if [ "$geo" != 'n' ]
then
  echo 'DELETE FROM geo_fr_department' | psql $db
  echo 'DELETE FROM geo_fr_region' | psql $db
  ./symfony doctrine:data-load --append data/fixtures/50-geo-fr-dpt+regions.yml
fi
echo ""
read -p "Do you want to update your french geographical data, with districts (can take a while)? [Y/n] " geo
if [ "$geo" != 'n' ]
then
  echo 'DELETE FROM geo_fr_district_base' | psql $db
  ./symfony doctrine:data-load --append data/fixtures/50-geo-fr-district.yml
fi

if [ $cities -eq 0 ]
then
echo ""
echo "Loading cities with INSEE code. It will take a couple of minutes..."
  ./symfony doctrine:data-load data/fixtures/20-postalcodes.yml --application=default
fi

echo ""
read -p "Do you want to add the new permissions? [Y/n] " add
if [ "$add" != 'n' ]
then
  echo "If you get Symfony errors in the next few actions, it is not a problem, the permissions simply exist already in the DB"
  echo ""
  #echo "Permission to access the colors of the pos module"
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v211-pos.yml
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v211-stats.yml
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v211-kiosk.yml
  echo "INSERT INTO sf_guard_group_permission (group_id, permission_id, created_at, updated_at) (SELECT (SELECT id FROM sf_guard_group WHERE name = 'pos-admin'), (SELECT id FROM sf_guard_permission WHERE name = 'pos-admin-color'), now(), now());" | psql
  #echo ""
fi

echo ''
echo ''
echo "Ensuring that permissions on directories are correct."
sudo chmod a+rwx web/uploads/

# Checking data...
i=0; for elt in `echo 'SELECT count(*) FROM ticket WHERE (printed_at IS NOT NULL OR integrated_at IS NOT NULL);' | psql`
do let "i++"; [ $i -eq 3 ] && NBTA=$elt; done
i=0; for elt in `echo 'SELECT count(*) FROM ticket WHERE (printed_at IS NOT NULL OR integrated_at IS NOT NULL) AND seat_id IS NOT NULL;' | psql`
do let "i++"; [ $i -eq 3 ] && NBPA=$elt; done
i=0; for elt in `echo 'SELECT count(*) FROM transaction;' | psql 2> /dev/null`
do let "i++";  [ $i -eq 3 ] && NBTRA=$elt; done

# final informations
echo ''
echo ''
if [ "$NBPA" -eq "$NBP" ] && [ "$NBT" -eq "$NBTA" ] && [ "$NBTR" -eq "$NBTRA" ]
then
  echo "Your migration went good. Your number of transactions, tickets and seated tickets is the same."
else
  echo "!! ERROR !! You had ${NBT} tickets for ${NBP} seated tickets, and ${NBTR} transactions ; you now have ${NBTA} tickets, ${NBPA} seated tickets and ${NBTRA} transactions!!!"
  echo "Do something..."
fi
echo ""
echo ""
echo "Those templates has no implementation and it can be missing: "
for elt in `find -iname '*.template'`; do [ ! -e `echo $elt | sed 's/.template$//'` ] && echo "TODO: $elt"; done
echo "end."
echo ""
echo ""
echo "Don't forget to configure those extra features:"
echo "- Check the different apps/*/config/*.yml.template to be sure that a apps/*/config/*.yml exists, create it if necessary"
echo '- Auto control on exit, for museum: you must add the execution of "./symfony e-venement:garbage-collector museum" every minute (in your crontab?)'

echo ""
echo "Don't forget to inform your users about those evolutions"
