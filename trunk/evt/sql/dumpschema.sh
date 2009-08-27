#!/bin/bash

SCHEMA=billeterie
DB=airelibre

pg_dump -x -O -sn $SCHEMA $DB > init.sql
