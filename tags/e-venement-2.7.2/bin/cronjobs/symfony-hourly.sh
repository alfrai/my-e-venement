#!/bin/bash

#EVENEMENT_DIRS='/var/www/e-venement.domain.tld'
EVENEMENT_DIRS=''
EVENEMENT_ROOT_DIRS=/var/www

if [ -n "$EVENEMENT_ROOT_DIRS" ]; then
for dir in `ls $EVENEMENT_ROOT_DIRS/`; do
if [ -d $EVENEMENT_ROOT_DIRS/$dir ]; then
EVENEMENT_DIRS="$EVENEMENT_DIRS ${dir}"
fi
done
fi

for dir in $EVENEMENT_DIRS; do
  echo $dir
  cd /var/www/$dir
  if [ -x ./symfony ]; then
    ./symfony project:send-emails --env=prod --application=rp
    ./symfony e-venement:send-manifestation-notifications
  fi
done
