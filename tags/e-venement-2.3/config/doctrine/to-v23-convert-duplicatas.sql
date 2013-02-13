UPDATE ticket t
SET duplicating = (SELECT id FROM ticket WHERE duplicate = t.id ORDER BY id LIMIT 1)
WHERE id IN (SELECT duplicate FROM ticket)
  AND duplicating IS NULL;
