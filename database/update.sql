USE ghe_db;

-- ========== 1. Nuevas tablas ==========
CREATE TABLE IF NOT EXISTS etapas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    orden INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS niveles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    etapa_id INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_nivel_etapa FOREIGN KEY (etapa_id) REFERENCES etapas(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_nivel_nombre_etapa (nombre, etapa_id)
);

-- ========== 2. Docentes: añadir tipo ==========
ALTER TABLE docentes ADD COLUMN IF NOT EXISTS tipo ENUM('tutor', 'especialista') NOT NULL DEFAULT 'tutor' AFTER apellido;

-- ========== 3. Migrar grupos.nivel -> niveles FK ==========
SET @etapaDefecto = (SELECT id FROM etapas WHERE nombre = 'Primaria' LIMIT 1);
SET @nivelMap = '';

-- Insertar niveles por defecto (1º-6º) si no hay niveles
INSERT IGNORE INTO etapas (nombre, orden) VALUES ('Infantil', 1), ('Primaria', 2), ('ESO', 3);

INSERT IGNORE INTO niveles (nombre, etapa_id, orden)
SELECT CONCAT(n, 'º'), e.id, n
FROM (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) nums
CROSS JOIN (SELECT id FROM etapas WHERE nombre = 'Primaria') e
WHERE NOT EXISTS (SELECT 1 FROM niveles WHERE nombre = CONCAT(n, 'º'));

-- Migrar grupos: convertir nivel VARCHAR a nivel_id INT
ALTER TABLE grupos ADD COLUMN nivel_id INT DEFAULT NULL AFTER nombre;
UPDATE grupos g
JOIN niveles n ON n.nombre = CONCAT(g.nivel, 'º')
SET g.nivel_id = n.id;
ALTER TABLE grupos MODIFY COLUMN nivel_id INT NOT NULL;
ALTER TABLE grupos ADD CONSTRAINT fk_grupo_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE;
ALTER TABLE grupos DROP COLUMN nivel;

-- ========== 4. Migrar nivel_asignatura.nivel -> nivel_id FK ==========
ALTER TABLE nivel_asignatura ADD COLUMN nivel_id INT DEFAULT NULL AFTER id;
UPDATE nivel_asignatura na
JOIN niveles n ON n.nombre = CONCAT(na.nivel, 'º')
SET na.nivel_id = n.id;
ALTER TABLE nivel_asignatura MODIFY COLUMN nivel_id INT NOT NULL;
ALTER TABLE nivel_asignatura DROP INDEX uniq_nivel_asig;
ALTER TABLE nivel_asignatura ADD UNIQUE KEY uniq_nivel_asig (nivel_id, asignatura_id);
ALTER TABLE nivel_asignatura ADD CONSTRAINT fk_na_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE;
ALTER TABLE nivel_asignatura DROP COLUMN nivel;

-- ========== 5. Añadir FK tutor si no existe ==========
SET @fkExists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_grupo_tutor' AND TABLE_NAME = 'grupos');
SET @sql = IF(@fkExists = 0, 'ALTER TABLE grupos ADD CONSTRAINT fk_grupo_tutor FOREIGN KEY (tutor_id) REFERENCES docentes(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
