#!/bin/bash

SCHEMA=vel
DB=e-venement

pg_dump -x -O -sn $SCHEMA $DB > init.sql
