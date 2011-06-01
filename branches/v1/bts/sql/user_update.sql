SET search_path TO flyspray;

UPDATE fly_users_private SET user_id = 4 WHERE user_id = 1;
UPDATE fly_users_in_groups SET user_id = 4 WHERE user_id = 1;
