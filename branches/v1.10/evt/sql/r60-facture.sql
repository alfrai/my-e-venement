SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = billeterie, pg_catalog;

alter table facture add column accountid bigint;
update facture set accountid = 4;
alter table facture alter column accountid set not null;
COMMENT ON COLUMN facture.accountid IS 'account.id';
ALTER TABLE ONLY facture
    ADD CONSTRAINT facture_accountid_fkey FOREIGN KEY (accountid) REFERENCES public.account(id) ON UPDATE CASCADE ON DELETE RESTRICT;
    
