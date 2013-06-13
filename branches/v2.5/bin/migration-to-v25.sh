#!/bin/bash

# preconditions
[ -z "$1" ] && echo "You forgot to specify your DB name as the first parameter" && exit 1;
[ -z "$2" ] && echo "You forgot to specify your user for DB access as the second parameter" && exit 2;
[ ! -d "data/sql" ] && echo "cd to your project's root directory please" && exit 3;
[ ! -z "$3" ] && echo "You'll do a restore-only procedure"

DB=$1
USER=$2

# preliminary modifications & backup
echo 'ALTER TABLE ticket DROP COLUMN duplicate;' | psql $DB
echo 'ALTER TABLE ticket_version DROP COLUMN duplicate;' | psql $DB
[ -z "$3" ] && pg_dump -Fc $DB > data/sql/$DB-`date +%Y%m%d`.pgdump && echo "DB dumped"

# recreation and data backup
dropdb $DB && createdb $DB && \
echo "GRANT ALL ON DATABASE $DB TO $USER" | psql $DB && \
./symfony doctrine:build  --all --no-confirmation && \
cat data/sql/$DB-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -a -d $DB && \
cat config/doctrine/functions-pgsql.sql | psql $DB && \
./symfony cc && ./symfony plugin:publish-assets

psql $DB <<EOF
UPDATE ticket SET printed_at = updated_at WHERE printed;
UPDATE ticket SET integrated_at = updated_at WHERE integrated;
UPDATE ticket_version SET printed_at = updated_at WHERE printed;
UPDATE ticket_version SET integrated_at = updated_at WHERE integrated;
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('pr-contact-relationships-admin', 'Permission to add and remove type of contacts relationships', '2013-06-03 13:14:58', '2013-06-03 13:14:58');
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) VALUES((SELECT last_value FROM sf_guard_permission_id_seq), (SELECT id FROM sf_guard_group WHERE name = 'pr-admin'), NOW(), NOW());
EOF
