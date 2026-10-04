-- Migracion para la base real ecoestudiantes (MariaDB 10.4, volcado 2026-10-04).
-- No borra tablas, no borra filas y no quita puntos_obtenidos.
-- Se puede ejecutar mas de una vez.

ALTER TABLE participantes ADD COLUMN IF NOT EXISTS nota DECIMAL(2,1) NULL;

-- Solo convierte quien participo y tenia puntos. El resto queda sin nota.
-- Con los datos actuales:
--   actividad 5 (max 15): duki 15 -> 5.0, nena 6 -> 2.0, nuevo inscrito -> NULL
--   actividad 9 (max 15): luis prueba 15 -> 5.0, juan perez inscrito -> NULL
UPDATE participantes pt
INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
SET pt.nota = LEAST(5.0, GREATEST(1.0, ROUND((pt.puntos_obtenidos / a.puntos) * 5, 1)))
WHERE pt.nota IS NULL
  AND pt.estado = 'Participó'
  AND pt.puntos_obtenidos > 0
  AND a.puntos > 0;
