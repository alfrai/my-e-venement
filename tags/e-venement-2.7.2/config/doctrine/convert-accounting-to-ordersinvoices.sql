INSERT INTO order_table ( id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version )
  SELECT id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version FROM accounting WHERE type = 'order';
INSERT INTO order_version (id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version)
  SELECT id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version FROM accounting_version WHERE type = 'order';

INSERT INTO invoice (id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version)
  SELECT id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version FROM accounting WHERE type = 'invoice';
INSERT INTO invoice_version (id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version)
  SELECT id, type, sf_guard_user_id, transaction_id, manifestation_id, created_at, updated_at, version FROM accounting_version WHERE type = 'invoice';

DELETE FROM accounting;
DELETE FROM accounting_version;

UPDATE raw_accounting SET invoice_id = accounting_id, accounting_id = NULL WHERE accounting_id IS NOT NULL;

SELECT setval('order_table_id_seq', (SELECT max(id)+1 FROM order_table), false);
SELECT setval('invoice_id_seq', (SELECT max(id)+1 FROM invoice), false);
