#!/bin/bash
# EXECUTE THIS SCRIPT BEFORE DOING A COMPLETE DB BACKUP, REBUILD AND RESTORE

# preconditions
[ ! -f ./symfony ] && echo 'The symfony file is not present... processus aborted' && exit 255
[ -z "$1" ] && echo 'You forgot the DB name as the first argument...' && exit 1
[ ! -z "$2" ] && echo 'You will do a restore-only procedure'
[ ! -z "$3" ] && echo "You'll use a specific DB user as $3" && DBU=$3
[ -z "$3" ] && DBU=$1
[ ! -d ./data/sql ] && echo 'The directory ./data/sql does not exist, cannot continue in that condition' && exit 2

if [ -z $2 ]
then

# pre-reorganization of data
psql $1 <<EOF
DROP TABLE vat;
CREATE TABLE vat (
    id SERIAL NOT NULL,
    name character varying(64) NOT NULL,
    value numeric(5,4) DEFAULT 0 NOT NULL,
    accounting_account character varying(50),
    version bigint,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL
);
DELETE FROM vat;
INSERT INTO vat(name,value,created_at,updated_at) (SELECT DISTINCT vat||'%' AS name, vat/100 AS value, now(), now() FROM manifestation);

ALTER TABLE manifestation ADD COLUMN vat_id INTEGER DEFAULT NULL;
UPDATE manifestation m SET vat_id = (SELECT id FROM vat WHERE value = m.vat/100 ORDER BY id LIMIT 1);
ALTER TABLE manifestation DROP COLUMN vat;

ALTER TABLE event_category ADD COLUMN vat_id INTEGER DEFAULT NULL;
UPDATE event_category ec SET vat_id = (SELECT id FROM vat WHERE value = ec.vat/100 ORDER BY id LIMIT 1);
ALTER TABLE event_category DROP COLUMN vat;

ALTER TABLE event ADD COLUMN sf_guard_user_id INTEGER DEFAULT NULL;
UPDATE event SET sf_guard_user_id = (SELECT MIN(id) FROM sf_guard_user);
ALTER TABLE manifestation ADD COLUMN sf_guard_user_id INTEGER DEFAULT NULL;
UPDATE manifestation SET sf_guard_user_id = (SELECT MIN(id) FROM sf_guard_user);
EOF

# backup
pg_dump -Fc $1 > data/sql/${1}-`date +%Y%m%d`.pgdump

fi

# rebuild + reinjection
rm -rf lib/model/doctrine/*/base lib/model/doctrine/packages/*/base
dropdb $1 && createdb $1 && echo "DATABASE RECREATED" &&
echo "GRANT ALL ON  DATABASE $1 TO $DBU" | psql $1    &&
php symfony doctrine:build --all --no-confirmation    &&
php symfony cc                                        &&
cat data/sql/$1-`date +%Y%m%d`.pgdump | pg_restore --disable-triggers -Fc -a -d $1
cat config/doctrine/functions-pgsql.sql | psql $1

# versionning
psql $1 <<EOF
UPDATE event SET version = 1 WHERE version IS NULL;
INSERT INTO event_version (SELECT * FROM event WHERE id NOT IN (SELECT id FROM event_version));

UPDATE manifestation SET version = 1 WHERE version IS NULL;
INSERT INTO manifestation_version (SELECT * FROM manifestation WHERE id NOT IN (SELECT id FROM manifestation_version));

UPDATE ticket tck SET vat = v.value FROM manifestation m, vat v WHERE v.id = m.vat_id AND m.id = tck.manifestation_id;
EOF

# adding necessary permissions
./symfony doctrine:data-load --append data/fixtures/11-permissions-v24.yml
psql $1 <<EOF
INSERT INTO sf_guard_group_permission(group_id, permission_id, created_at, updated_at)
  VALUES (
    (SELECT id FROM sf_guard_group WHERE name = 'event-admin'),
    (SELECT id FROM sf_guard_permission WHERE name = 'event-admin-vat'),
    now(), now()
  );
EOF
