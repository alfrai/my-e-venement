#!/bin/bash

SCHEMA=billeterie
DB=contact

pg_dump -x -O -sn $SCHEMA $DB > init.sql
