#!/bin/bash

SCHEMA=billeterie
DB=ttt

pg_dump -x -O -sn $SCHEMA $DB > init.sql
