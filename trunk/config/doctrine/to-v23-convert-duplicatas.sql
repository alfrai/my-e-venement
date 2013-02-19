UPDATE ticket t
SET duplicating = (SELECT id FROM ticket WHERE duplicate = t.id ORDER BY id LIMIT 1)
WHERE id IN (SELECT duplicate FROM ticket)
  AND duplicating IS NULL;

UPDATE ticket t
SET cancelling = (SELECT duplicating FROM ticket t2 WHERE t2.id = t.cancelling)
WHERE cancelling IS NOT NULL
  AND cancelling IN (SELECT id FROM ticket WHERE duplicating IS NOT NULL)
  AND id IN (SELECT duplicate FROM ticket)
  AND duplicating IS NULL;
