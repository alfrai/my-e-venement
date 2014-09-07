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
#[ ! -d "data/sql" ] && echo "cd to your project's root directory please" && exit 3;

[ -z "$1" ] && echo "You must specify the DB user that is used by e-venement as the first parameter" && exit 1
SFUSER="$1"
[ -n "$2" ] && export PGDATABASE="$2"
[ -n "$3" ] && export PGUSER="$3"
[ -n "$4" ] && export PGHOST="$4"
[ -n "$5" ] && export PGPORT="$5"


echo "Usage: bin/migrate-to-v28.sh SFUSER [DB [USER [HOST [PORT]]]]"
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
echo "Loading data..."

db="$PGDATABASE"
[ -z "$db" ] && db=$USER

cat data/sql/$db-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -a -d $db && \
echo "  ... done."

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
echo "Permissions & groups for accessing taxes..."
./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-taxes.yml
echo "Permissions & groups for accessing online sales stats..."
./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-stats.yml
echo "Permissions & groups for accessing the store..."
./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-pos.yml
echo "Permissions & groups for reducing the value of tickets, one by one..."
./symfony doctrine:data-load --append data/fixtures/11-permissions-v28-tck.yml
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
echo "- If this plateform needs Passbooks, do not forget to set them up in the apps pub & tck"
echo "- If this plateform is using QRCodes, think to move the app_seller_salt from apps/tck/config/app.yml to project_eticketting_salt in config/project.yml"
echo "- Checkout config/autoload.inc.php.template and complete your config/autoload.inc.php in that way..."
echo "- IMPORTANT: the management of extra modules has evoluated, it has moved from config/extra-modules.php to config/project.yml, DO NOT FORGET IT!"

echo ""
echo "Don't forget to inform your users about those evolutions"
