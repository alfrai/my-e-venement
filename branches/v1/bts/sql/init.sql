--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = flyspray, pg_catalog;

ALTER TABLE fly_users RENAME TO fly_users_private;

ALTER TABLE fly_users_private
	DROP COLUMN user_name;
ALTER TABLE fly_users_private
	DROP COLUMN real_name;
ALTER TABLE fly_users_private
	DROP COLUMN user_pass;
ALTER TABLE fly_users_private
	DROP COLUMN email_address;

CREATE OR REPLACE VIEW fly_users AS
    SELECT account.id, account."login" AS user_name, account."password" AS user_pass,
    	   account.name AS real_name, account.email AS email_address, fly.user_id, fly.account_enabled,
    	   fly.jabber_id, fly.notify_type, fly.notify_own, fly.dateformat, fly.dateformat_extended,
    	   fly.magic_url, fly.tasks_perpage, fly.register_date, fly.time_zone, fly.login_attempts,
    	   fly.lock_until
    FROM fly_users_private fly, public.account account
    WHERE (account.id = fly.user_id);

COMMENT ON VIEW fly_users IS 'Permet de faire le lien entre public.account (e-venement) et users_private (table provenant de "users") (flyspray)';

CREATE RULE fly_users_insert AS ON INSERT TO fly_users
    DO INSTEAD NOTHING;
CREATE RULE fly_users_insert AS ON DELETE TO fly_users
    DO INSTEAD NOTHING;

CREATE RULE fly_users_update AS ON UPDATE TO fly_users
    DO INSTEAD
    UPDATE fly_users_private
       SET jabber_id = NEW.jabber_id,
           notify_type = NEW.notify_type,
           notify_own = NEW.notify_own,
           dateformat = NEW.dateformat,
           dateformat_extended = NEW.dateformat_extended,
           magic_url = NEW.magic_url,
           tasks_perpage = NEW.tasks_perpage,
           register_date = NEW.register_date,
           time_zone = NEW.time_zone,
           login_attempts = NEW.login_attempts,
           lock_until = NEW.lock_until
     WHERE user_id = OLD.user_id;
