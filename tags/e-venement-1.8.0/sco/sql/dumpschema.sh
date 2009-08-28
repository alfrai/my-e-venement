#!/bin/bash

SCHEMA=sco
DB=contact

pg_dump -x -O -sn $SCHEMA $DB > init.sql
