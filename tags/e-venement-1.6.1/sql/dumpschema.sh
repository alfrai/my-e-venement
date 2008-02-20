#!/bin/bash

SCHEMA=public
DB=contact

pg_dump -x -O -sn $SCHEMA $DB > init.sql
