#!/bin/bash

SCHEMA=vel
DB=contact

pg_dump -x -O -sn $SCHEMA $DB > init.sql
