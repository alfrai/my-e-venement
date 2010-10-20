#!/bin/bash

SCHEMA=pro
DB=contact

pg_dump -x -O -sn $SCHEMA $DB > init.sql
