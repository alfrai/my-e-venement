SET search_path = billeterie, pg_catalog;

ALTER TABLE transaction ADD COLUMN spaceid integer;
ALTER TABLE ONLY transaction
    ADD CONSTRAINT transaction_spaceid_fkey FOREIGN KEY (spaceid) REFERENCES space(id) ON UPDATE CASCADE ON DELETE RESTRICT;
    

