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
[ -z "$1" ] && echo "You forgot to specify your DB name as the first parameter" && exit 1;
[ -z "$2" ] && echo "You forgot to specify your user for DB access as the second parameter" && exit 2;
[ ! -d "data/sql" ] && echo "cd to your project's root directory please" && exit 3;
[ ! -z "$3" ] && echo "You'll do a restore-only procedure"

DB=$1
USER=$2

echo "Are you sure you want to continue with those parameters :"
echo Database: $DB
echo User: $USER
[ ! -z "$3" ] && echo "With a restore only procedure (no DB backup, no previous backup overwritting)"
[ -z "$3" ] && echo "With a backup which is going to be written in data/sql/$DB-`date +%Y%m%d`.pgdump"
echo ""
echo "To continue press ENTER"
echo "To cancel press CTRL+C NOW !!"
read

if [ -z "$3" ]; then

echo "DUMPING DB..."
pg_dump -Fc $DB > data/sql/$DB-`date +%Y%m%d`.before.pgdump && echo "DB pre dumped"

# preliminary modifications & backup
psql $DB <<EOF
  DROP TABLE seating_plan;
  ALTER TABLE transaction DROP COLUMN workspace_id;
  ALTER TABLE transaction_version DROP COLUMN workspace_id;
  UPDATE ticket SET numerotation = NULL WHERE trim(numerotation) = '';
EOF

echo "DUMPING DB..."
pg_dump -Fc $DB > data/sql/$DB-`date +%Y%m%d`.pgdump && echo "DB dumped"

fi #end of "allow dumps" condition

echo ""
read -p "Do you want to resset properly your lib/model, lib/form & lib/filter files using SVN ? [y/N] " reset
if [ "$reset" = 'y' ]; then
  rm -rf lib/*/doctrine/
  svn update
fi

# recreation and data backup
dropdb $DB && createdb $DB && \
echo "GRANT ALL ON DATABASE $DB TO $USER" | psql $DB && \
./symfony doctrine:build  --all --no-confirmation && \
cat data/sql/$DB-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -d $DB -a
cat config/doctrine/functions-pgsql.sql | psql $DB && \
./symfony cc &> /dev/null
echo ""

[ ! -f apps/default/config/app.yml ] && cp apps/default/config/app.yml.template apps/default/config/app.yml

echo ""
echo "Be careful with DB errors. A table with an error is an empty table !... If necessary take back the DB backup and correct things by hand before to retry this migration script."
echo ""

# final data modifications
echo ""
echo "Creating permissions for seated plans features"
psql $DB <<EOF
-- seated plan access
INSERT INTO sf_guard_group(name, description, created_at, updated_at) VALUES ('event-seated-plan', 'Ability to manage seated plans', now(), now());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-seated-plan', 'Permission to see seated plans', now(), now());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-seated-plan-new', 'Permission to create seated plans', now(), now());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-seated-plan-edit', 'Permission to edit seated plans', now(), now());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-seated-plan-del', 'Permission to delete seated plans', now(), now());
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) (SELECT id, (SELECT id FROM sf_guard_group WHERE name = 'event-seated-plan'), NOW(), NOW() FROM sf_guard_permission WHERE name IN ('event-seated-plan', 'event-seated-plan-new', 'event-seated-plan-edit', 'event-seated-plan-del'));

-- seated ticketting access
INSERT INTO sf_guard_group(name, description, created_at, updated_at) VALUES ('tck-seated', 'Ability to Ability to deal with seated ticketting', now(), now());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('tck-seat-allocation', 'Permission to allocate a seat', now(), now());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-seats-allocation', 'Permission to display the seats allocation in the event module', now(), now());
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) (SELECT id, (SELECT id FROM sf_guard_group WHERE name = 'tck-seated'), NOW(), NOW() FROM sf_guard_permission WHERE name IN ('tck-seat-allocation','event-seated-allocation'));
EOF

echo ""
echo ""
echo "Patching framework..."
for elt in data/diff/*.diff
do
  patch -N -p0 < $elt
done
rm -f `find lib/vendor/ -iname '*.rej'`
rm -f `find lib/vendor/ -iname '*.orig'`

# final informations
echo ""
echo ""
echo "Don't forget to configure those extra features :"
echo "- Seated plans for your locations"
echo "- Setup again the e-venement Messaging Network : http://[YOUR E-VENEMENT BASE ROOT]/liJappixPlugin"

echo ""
echo "Don't forget to inform your users about those evolutions"
