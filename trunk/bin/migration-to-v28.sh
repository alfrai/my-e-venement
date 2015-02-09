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


echo "Usage: bin/pre-mirate-to-v28.sh SFUSER [DB [USER [HOST [PORT]]]]"
echo "Are you sure you want to continue with those parameters :"
echo "The e-venement's DB user: $SFUSER"
echo "Database: $PGDATABASE"
echo "User: $PGUSER"
echo "Host: $PGHOST"
echo "Port: $PGPORT"
echo ""
echo "- Please check config/autoload.inc.php.template and complete config/autoload.inc.php in that way..."
echo ""
echo "To continue press ENTER"
echo "To cancel press CTRL+C NOW !!"
read


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

read -p "Do you want to reset your dump & patch your database for e-venement v2.8 ? [Y/n] " dump
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
  
  ALTER TABLE web_origin ADD COLUMN user_agent TEXT DEFAULT 'Unknown';
  
  CREATE TABLE price_translation (
    id bigint NOT NULL,
    name character varying(63) NOT NULL,
    description character varying(255),
    lang character(2) NOT NULL
  );
  INSERT INTO price_translation (SELECT id, name, description, 'en' AS lang FROM price WHERE (id,'en') NOT IN (SELECT id,lang FROM price_translation));
  ALTER TABLE price DROP COLUMN name;
  ALTER TABLE price DROP COLUMN description;
  
  CREATE TABLE member_card_type_translation (
    id bigint NOT NULL,
    description character varying(255),
    lang character(2) NOT NULL
  );
  INSERT INTO member_card_type_translation (SELECT id, description, 'en' AS lang FROM member_card_type WHERE (id,'en') NOT IN (SELECT id,lang FROM member_card_type_translation));
  ALTER TABLE member_card_type DROP COLUMN description;
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

echo "Resetting the DB"
echo ""
# recreation and data backup
# those rm -rf cache/* are hacks to avoid cache related segfaults...
dropdb $db;
createdb $db

last=$?
rm -rf cache/*
[ $last -eq 0 ] && ./symfony doctrine:drop-db --no-confirmation && ./symfony doctrine:build-db
last=$?
rm -rf cache/*
[ $last -eq 0 ] && ./symfony doctrine:build-model
last=$?
rm -rf cache/*
[ $last -eq 0 ] && ./symfony doctrine:build-forms
last=$?
rm -rf cache/*
[ $last -eq 0 ] && ./symfony doctrine:build-filters
last=$?
rm -rf cache/*
[ $last -eq 0 ] && ./symfony doctrine:build-sql
last=$?
rm -rf cache/*
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

# final data modifications
echo ""
read -p "Do you want to copy Price's english translations (default i18n after a migration from v2.7) into french ? [Y/n] " reset
[ "$reset" != 'n' ] && ./symfony e-venement:copy-i18n Price en fr
read -p "Do you want to copy MemberCardType's english translations (default i18n after a migration from v2.7) into french ? [Y/n] " reset
[ "$reset" != 'n' ] && ./symfony e-venement:copy-i18n MemberCardType en fr

echo ""
read -p "Do you want to add the new permissions? [Y/n] " add
read -p "Do you want to reset the permissions related to the holds? [y/N] " reset
if [ "$add" != 'n' ]
then
  echo "If you will get Symfony errors in the next few actions, it is not a problem, the permissions just already exist in the DB"
  echo ""
  echo "Permissions & groups for the grp module, if they are not yet present (an error here is a good thing...)"
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-grp.yml
  echo "Permissions & groups for surveys..."
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-srv.yml
  echo "Permissions & groups for accessing backups..."
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-backups.yml
  echo "Permissions & groups for accessing taxes..."
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-taxes.yml
  echo "Permissions & groups for accessing online sales stats..."
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-stats.yml
  echo "Permissions & groups for accessing the store..."
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-pos.yml
  echo "Permissions & groups for reducing the value of tickets, one by one..."
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-tck.yml
  echo "Permissions & groups for holds..."
  if [ "$reset" != 'n' ]
  then
    psql $db <<EOF
      DELETE FROM sf_guard_permission WHERE name LIKE 'event-hold%';
      DELETE FROM sf_guard_group WHERE name LIKE 'event-hold%';
EOF
  fi
  ./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-hold.yml
fi

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

echo ''
echo "Changing (or not) file permissions for the e-venement Messaging Network ..."
chmod -R 777 web/liJappixPlugin/store web/liJappixPlugin/tmp web/liJappixPlugin/log &> /dev/null
echo "... done."

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
  echo "!! ERROR !! You had ${NBT} tickets for ${NBP} seated tickets, and ${NBTR} tranasction ; you now have ${NBTA} tickets, ${NBPA} seated tickets and ${NBTRA} transactions!!!"
  echo "Do something..."
fi
echo ""
echo ""
echo "Don't forget to configure those extra features:"
echo "- Check the different apps/*/config/*.yml.template to be sure that a apps/*/config/*.yml exists, create it if necessary"
echo "- Change the apps/*/config/factories.yml to replace sfMailer with liMailer and Swift_DoctrineSpool with liSpool, and correct your scripts to use the task e-venement:send-emails --time-limit=XX instead of project:send-emails"
echo "- e-venement Messaging Network: rm -rf web/liJappixPlugin; svn update; then run http[s]://[YOUR E-VENEMENT BASE ROOT]/liJappixPlugin"
echo "- If this plateform needs Passbooks, do not forget to set them up in the apps pub & tck"
echo "- If this plateform is using QRCodes, think to move the app_seller_salt from apps/tck/config/app.yml to project_eticketting_salt in config/project.yml"
echo "- IMPORTANT: the management of extra modules has evoluated, it has moved from config/extra-modules.php to config/project.yml, DO NOT FORGET IT!"

echo ""
echo "Don't forget to inform your users about those evolutions"
