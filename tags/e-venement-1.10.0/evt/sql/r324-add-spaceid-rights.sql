SET search_path = billeterie, pg_catalog;
ALTER TABLE rights ADD COLUMN spaceid integer;
ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_spaceid_fkey FOREIGN KEY (spaceid) REFERENCES space(id) ON UPDATE CASCADE ON DELETE RESTRICT;
    
