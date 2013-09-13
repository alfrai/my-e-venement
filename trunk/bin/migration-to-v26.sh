#!/bin/bash

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

# preliminary modifications & backup
psql $DB <<EOF
ALTER TABLE ticket DROP COLUMN duplicate;
ALTER TABLE ticket DROP COLUMN printed;
ALTER TABLE ticket DROP COLUMN integrated;
ALTER TABLE ticket_version DROP COLUMN duplicate;
ALTER TABLE ticket_version DROP COLUMN printed;
ALTER TABLE ticket_version DROP COLUMN integrated;

ALTER TABLE manifestation DROP COLUMN optional;
ALTER TABLE manifestation_version DROP COLUMN optional;
ALTER TABLE manifestation ADD COLUMN blocking BOOLEAN;
ALTER TABLE manifestation ADD COLUMN reservation_begins_at TIMESTAMP;
ALTER TABLE manifestation ADD COLUMN reservation_ends_at TIMESTAMP;
ALTER TABLE manifestation ADD COLUMN reservation_confirmed BOOLEAN;
ALTER TABLE manifestation ADD COLUMN reservation_description BOOLEAN;
ALTER TABLE manifestation ADD COLUMN reservation_optional BOOLEAN;
ALTER TABLE manifestation_version ADD COLUMN blocking BOOLEAN;
ALTER TABLE manifestation_version ADD COLUMN reservation_begins_at TIMESTAMP;
ALTER TABLE manifestation_version ADD COLUMN reservation_ends_at TIMESTAMP;
ALTER TABLE manifestation_version ADD COLUMN reservation_confirmed BOOLEAN;
ALTER TABLE manifestation_version ADD COLUMN reservation_description BOOLEAN;
ALTER TABLE manifestation_version ADD COLUMN reservation_optional BOOLEAN;
UPDATE manifestation SET
  reservation_begins_at = happens_at,
  reservation_ends_at = happens_at + (duration||' second')::interval,
  reservation_confirmed = true,
  reservation_optional = false,
  blocking = true
--WHERE reservation_begins_at IS NULL
--  AND reservation_ends_at IS NULL
--  AND reservation_confirmed IS NULL
;
UPDATE manifestation_version SET
  reservation_begins_at = happens_at,
  reservation_ends_at = happens_at + (duration||' second')::interval,
  reservation_confirmed = true,
  reservation_optional = false,
  blocking = true
--WHERE reservation_begins_at IS NULL
--  AND reservation_ends_at IS NULL
--  AND reservation_confirmed IS NULL
;

ALTER TABLE vat ADD COLUMN created_at timestamp without time zone;
ALTER TABLE vat ADD COLUMN updated_at timestamp without time zone;
ALTER TABLE vat_version ADD COLUMN created_at timestamp without time zone;
ALTER TABLE vat_version ADD COLUMN updated_at timestamp without time zone;
UPDATE vat SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL AND updated_at IS NULL;
UPDATE vat_version SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL AND updated_at IS NULL;

ALTER TABLE location ADD COLUMN place BOOLEAN DEFAULT true NOT NULL;
EOF

echo "DUMPING DB..."
pg_dump -Fc $DB > data/sql/$DB-`date +%Y%m%d`.pgdump && echo "DB dumped"

echo ""
echo To continue press ENTER
read

fi #end of "allow dumps" condition

# recreation and data backup
dropdb $DB && createdb $DB && \
echo "GRANT ALL ON DATABASE $DB TO $USER" | psql $DB && \
./symfony doctrine:build  --all --no-confirmation && \
cat data/sql/$DB-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -d $DB -a
cat config/doctrine/functions-pgsql.sql | psql $DB && \
./symfony cc &> /dev/null
echo ""

# final data modifications
echo "Adding required permissions and groups";
echo " - member cards (correction)"
psql $DB <<EOF
-- mc / pr
UPDATE sf_guard_permission SET name = 'pr-card-admin' WHERE name = ' pr-card-admin';
EOF

echo " - resources reservation"
psql $DB <<EOF
-- event
INSERT INTO sf_guard_group(name, description, created_at, updated_at) VALUES ('event-reservation-admin', 'Permission to manage reservations', '2013-06-17 17:14:50', '2013-06-17 17:14:50');
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-reservation-change-contact', 'Permission to change the contact of any reservation', '2013-06-17 17:14:50', '2013-06-17 17:14:50');
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) VALUES((SELECT last_value FROM sf_guard_permission_id_seq), (SELECT last_value FROM sf_guard_group_id_seq), NOW(), NOW());
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('event-reservation-confirm', 'Permission to confirm a reservation', '2013-06-17 17:14:50', '2013-06-17 17:14:50');
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) VALUES((SELECT last_value FROM sf_guard_permission_id_seq), (SELECT last_value FROM sf_guard_group_id_seq), NOW(), NOW());
INSERT INTO sf_guard_user_group(group_id, user_id, created_at, updated_at) (SELECT (SELECT id FROM sf_guard_group WHERE name = 'event-reservation-admin'), user_id, now(), now() FROM sf_guard_user_group ug LEFT JOIN sf_guard_group g ON g.id = ug.group_id WHERE g.name = 'event-admin');
EOF

echo " - stats"
psql $DB <<EOF
-- stats
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('stats-pr-social', 'Permission to access to social stats', '2013-07-22 10:14:58', '2013-07-22 10:14:58');
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) VALUES((SELECT last_value FROM sf_guard_permission_id_seq), (SELECT id FROM sf_guard_group WHERE name = 'pr-social'), NOW(), NOW());
INSERT INTO group_user(group_id, sf_guard_user_id, updated_at, created_at) (select g.id, u.id, now(), now() from group_table g, sf_guard_user u where g.id IS NOT NULL AND g.sf_guard_user_id is null);
INSERT INTO sf_guard_permission(name, description, created_at, updated_at) VALUES ('stats-pr-groups', 'Permission to access the groups evolution statistics', '2013-08-15 10:14:50', '2013-08-15 10:14:50');
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) VALUES((SELECT last_value FROM sf_guard_permission_id_seq), (SELECT id FROM sf_guard_group WHERE name = 'stats-others'), NOW(), NOW());
EOF

echo " - workspaces"
psql $DB <<EOF
-- ws
INSERT INTO sf_guard_permission(name, description, updated_at, created_at) VALUES ('ws-group', 'Permission to associate automatically a group to a contact created online', now(), now());
INSERT INTO sf_guard_group(name, description, updated_at, created_at) VALUES ('ws-group', 'Associate automatically a group to a contact created online', now(), now());
INSERT INTO sf_guard_group_permission(permission_id, group_id, created_at, updated_at) VALUES ((SELECT id FROM sf_guard_permission WHERE name = 'ws-group'), (SELECT id FROM sf_guard_group WHERE name = 'ws-group'), now(), now());
INSERT INTO sf_guard_user_group (SELECT id, (SELECT id FROM sf_guard_group WHERE name = 'ws-group'), now(), now() FROM sf_guard_user WHERE id in (SELECT ug.user_id FROM sf_guard_user_group ug LEFT JOIN sf_guard_group g ON g.id = ug.group_id WHERE g.name IN ('admin', 'tck-admin')));
EOF

echo ""
echo "Correcting a postalcode on BIARD, France"
psql $DB <<EOF
-- other
UPDATE postalcode SET postalcode = '86580' WHERE city = 'BIARD' AND postalcode = '86000';
EOF

# final informations
echo ""
echo ""
echo "Don't forget to configure those extra features :"
echo "e-venement messaging system: http://[YOUR E-VENEMENT BASE ROOT]/liJappixPlugin + config/project.yml + per-users settings"

echo ""
echo "Don't forget to add some users into the event-reservations-admin group"

echo ""
echo "Don't forget to inform your users about those evolutions"
