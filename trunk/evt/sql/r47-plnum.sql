--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = billeterie, pg_catalog;

ALTER TABLE ONLY reservation_pre
    DROP CONSTRAINT reservation_pre_plnum_ukey;
ALTER TABLE ONLY reservation_pre
    ADD CONSTRAINT reservation_pre_plnum_ukey UNIQUE (manifid, plnum, annul);
