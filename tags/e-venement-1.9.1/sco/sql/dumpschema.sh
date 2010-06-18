#!/bin/bash

SCHEMA=sco
DB=ttt

pg_dump -x -O -sn $SCHEMA $DB > init.sql
