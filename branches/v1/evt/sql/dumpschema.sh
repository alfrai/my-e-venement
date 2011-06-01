#!/bin/bash

SCHEMA=billeterie
DB=e-venement

pg_dump -x -O -sn $SCHEMA $DB > init.sql
