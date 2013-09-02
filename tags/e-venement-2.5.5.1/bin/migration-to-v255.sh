#!/bin/bash

# preconditions
[ -z "$1" ] && echo "You forgot to specify your DB name as the first parameter" && exit 1;
[ -z "$2" ] && echo "You forgot to specify your user for DB access as the second parameter" && exit 2;
[ ! -d "data/sql" ] && echo "cd to your project's root directory please" && exit 3;

DB=$1
USER=$2

psql $DB <<EOF
UPDATE sf_guard_permission SET name = 'pr-card-admin' WHERE name = ' pr-card-admin';
EOF
