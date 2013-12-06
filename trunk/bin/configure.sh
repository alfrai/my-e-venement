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

echo "To access your DB, you must give arguments to this script : bin/configure.sh [DB [USER [SERVER [PORT]]]]"
echo "You need, at least, to have already created a working PostgreSQL account and have a valid server on which you can create databases."
echo "Those connection informations are related to YOU, not necessarly your e-venement DB access. You or your e-venement DB account must have the permission to create databases"
echo ""
read -p "Are you ready to configure e-venement ? [Y/n] " ready
[ "$ready" = 'n' ] && exit 1

[ -n "$1" ] && export PGDATABASE=$1
[ -n "$2" ] && export PGUSER=$2
[ -n "$3" ] && export PGHOST=$3
[ -n "$4" ] && export PGPOST=$4

[ ! -f "bin/configure.sh" ] && echo "cd to your project's root directory please" && exit 3;

continue=y
[ -z "$PGDATABASE" ] && read -p "Are you sure you want to continue without specifying your database (bin/configure.sh [DB ...]) ? [y/N] " continue
[ "$continue" != 'y' ] && exit 4

echo "Do you want to replace all the existing e-venement configuration by templates ?"
read -p "(type 'y' if you are installing from scratch) [y/N] " erase
echo ""

[ "$erase" = 'y' ] && \
for elt in `find apps/*/config/ config/ -iname '*.template'`; do FINAL=`echo $elt | sed 's/.template$//'`; cp $elt $FINAL; done
echo "The previous configuration has be replaced by default template files. Now you will have to edit them (with the nano editor)."
echo "Press CTRL+X to exit a file edition."
read
for elt in `find apps/*/config/ config/ -iname '*.template'`; do FINAL=`echo $elt | sed 's/.template$//'`; nano -i $FINAL; done

echo ""
read -p "Do you want to create the DB framework and the Symfony model/forms/filters (careful, it will erase any existing DB) ? [Y/n] " db
if [ "$db" != 'n' ]; then
  read -p "Do you want to backup any existing DB ? [Y/n] " db
  if [ "$db" != 'n' ]; then
    [ ! -d data/sql ] && mkdir data/sql
    pg_dump -Fc > data/sql/db-`date +%Y%m%d%H%i%s`.pgdump
  fi
  
  [ -n "$PGDATABASE" ] && dropdb $PGDATABASE
  createdb
  read -p "Can you precise again the name of your e-venement's DB user ? " pguser
  [ -n "$pguser" ] && echo "GRANT ALL ON DATABASE $PGDATABASE TO $pguser" | psql
  
  ./symfony doctrine:build --all --no-confirmation
fi


read -p "Do you want to create the first superuser account ? [Y/n] " su
if [ "$su" != 'n' ]; then
  again=1;
  while [ "$again" -gt 0 ]; do
    read -p "Give us the superuser's login id: " login
    read -p "Give us the superuser's password: " passwd
    read -p "Again: " passwd2
    if [ "$passwd" = "$passwd2" ] && [ -n "$login" ]; then
      read -p "Give us the user's full name: " name
      if [ -n "$again" ]; then
        again=0
        ./symfony guard:create-user mail@example.com $login $passwd $name && \
        ./symfony guard:promote "$login" && \
        echo "The user $login has been created successfully."
        echo ""
      fi
    else
      echo "The passwords do not match. Try again."
    fi
  done
fi

read -p "Do you want to load some fixtures ? [y/N] " fixtures
if [ "$fixtures" = 'y' ]; then
  read -p "Load permissions framework ? [Y/n] " f
  [ "$f" != 'n' ] && ./symfony doctrine:data-load --append data/fixtures/10-permissions.yml
  read -p "Load basic meta-datas (in french) ? [Y/n] " f
  [ "$f" != 'n' ] && ./symfony doctrine:data-load --append data/fixtures/60-generic-data.yml
  read -p "Load french geo-datas (postalcodes, regions, ...) ? [Y/n] " f
  [ "$f" != 'n' ] && ./symfony doctrine:data-load --append data/fixtures/20-postalcodes.yml data/fixtures/50-geo-fr-data.yml
  
  read -p "Load basic & not-so-relevant demo data for Public Relations & demo accounts ? [N/y] " f
  [ "$f" = 'y' ] && ./symfony doctrine:data-load --append data/fixtures/30-demo.yml data/fixtures/40-accounts-demo.yml
  read -p "Load seated-plan (pertinent) data ? [Y/n] "
  [ "$f" != 'n' ] && ./symfony doctrine:data-load --append data/fixtures/41-seated-plans-demo.yml
fi

echo ""
echo "ONLINE SALES: Do not forget to create the specific user and the specific payment method for online sales"
echo "e-venement Messaging Network: Do not forget to create/give Jabber accounts to your users"

echo ""
echo "Thank you for using e-venement. Enjoy your experience and join the crew on http://www.e-venement.org/"

echo ""
