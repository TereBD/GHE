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

-- ========== 2. Docentes: añadir tipo si no existe ==========
SET @colExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'ghe_db' AND TABLE_NAME = 'docentes' AND COLUMN_NAME = 'tipo');
SET @sql = IF(@colExists = 0,
    'ALTER TABLE docentes ADD COLUMN tipo ENUM(''tutor'', ''especialista'') NOT NULL DEFAULT ''tutor'' AFTER apellido',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========== 3. Migrar grupos.nivel -> niveles FK ==========
SET @colNivelExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'ghe_db' AND TABLE_NAME = 'grupos' AND COLUMN_NAME = 'nivel');
SET @colNivelIdExists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'ghe_db' AND TABLE_NAME = 'grupos' AND COLUMN_NAME = 'nivel_id');

-- Solo migrar si la columna vieja existe y la nueva no
SET @migrarGrupos = IF(@colNivelExists > 0 AND @colNivelIdExists = 0, 1, 0);

-- Insertar etapas y niveles por defecto si no existen
INSERT IGNORE INTO etapas (nombre, orden) VALUES ('Infantil', 1), ('Primaria', 2), ('ESO', 3), ('Proyecto', 99);

-- Crear niveles del 1º al 6º de Primaria si no existen
INSERT IGNORE INTO niveles (nombre, etapa_id, orden)
SELECT CONCAT(n, 'º'), e.id, n
FROM (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3
    UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
) nums
CROSS JOIN (SELECT id FROM etapas WHERE nombre = 'Primaria') e;

-- Si grupos tiene nivel VARCHAR y necesitamos migrar
SET @sql2 = IF(@migrarGrupos = 1,
    'ALTER TABLE grupos ADD COLUMN nivel_id INT DEFAULT NULL AFTER nombre',
    'SELECT 1');
PREPARE stmt FROM @sql2;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Poblar nivel_id en grupos desde el nombre del nivel
UPDATE grupos g
JOIN niveles n ON n.nombre = CONCAT(g.nivel, 'º')
SET g.nivel_id = n.id
WHERE g.nivel_id IS NULL AND g.nivel IS NOT NULL AND g.nivel != '';

-- Para grupos Proyecto que tengan nivel = 'PROYECTO'
UPDATE grupos g
JOIN niveles n ON n.nombre = 'Proyecto'
SET g.nivel_id = n.id
WHERE g.nivel_id IS NULL AND g.nivel = 'PROYECTO';

-- Hacer nivel_id NOT NULL
SET @sql3 = IF(@migrarGrupos = 1,
    'ALTER TABLE grupos MODIFY COLUMN nivel_id INT NOT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql3;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Añadir FK si no existe
SET @fkGrupoNivel = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_grupo_nivel' AND TABLE_NAME = 'grupos' AND CONSTRAINT_SCHEMA = 'ghe_db');
SET @sql4 = IF(@fkGrupoNivel = 0 AND @migrarGrupos = 1,
    'ALTER TABLE grupos ADD CONSTRAINT fk_grupo_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql4;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Eliminar columna vieja nivel
SET @sql5 = IF(@migrarGrupos = 1,
    'ALTER TABLE grupos DROP COLUMN nivel',
    'SELECT 1');
PREPARE stmt FROM @sql5;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========== 4. Migrar nivel_asignatura.nivel -> nivel_id FK ==========
SET @naColNivel = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'ghe_db' AND TABLE_NAME = 'nivel_asignatura' AND COLUMN_NAME = 'nivel');
SET @naColNivelId = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'ghe_db' AND TABLE_NAME = 'nivel_asignatura' AND COLUMN_NAME = 'nivel_id');
SET @migrarNA = IF(@naColNivel > 0 AND @naColNivelId = 0, 1, 0);

SET @sql6 = IF(@migrarNA = 1,
    'ALTER TABLE nivel_asignatura ADD COLUMN nivel_id INT DEFAULT NULL AFTER id',
    'SELECT 1');
PREPARE stmt FROM @sql6;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE nivel_asignatura na
JOIN niveles n ON n.nombre = CONCAT(na.nivel, 'º')
SET na.nivel_id = n.id
WHERE na.nivel_id IS NULL AND na.nivel IS NOT NULL AND na.nivel != '';

SET @sql7 = IF(@migrarNA = 1,
    'ALTER TABLE nivel_asignatura MODIFY COLUMN nivel_id INT NOT NULL',
    'SELECT 1');
PREPARE stmt FROM @sql7;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Recrear UNIQUE KEY y FK
SET @sql8 = IF(@migrarNA = 1,
    'ALTER TABLE nivel_asignatura DROP INDEX uniq_nivel_asig',
    'SELECT 1');
PREPARE stmt FROM @sql8;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql9 = IF(@migrarNA = 1,
    'ALTER TABLE nivel_asignatura ADD UNIQUE KEY uniq_nivel_asig (nivel_id, asignatura_id)',
    'SELECT 1');
PREPARE stmt FROM @sql9;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fkNaNivel = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_na_nivel' AND TABLE_NAME = 'nivel_asignatura' AND CONSTRAINT_SCHEMA = 'ghe_db');
SET @sql10 = IF(@fkNaNivel = 0 AND @migrarNA = 1,
    'ALTER TABLE nivel_asignatura ADD CONSTRAINT fk_na_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql10;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql11 = IF(@migrarNA = 1,
    'ALTER TABLE nivel_asignatura DROP COLUMN nivel',
    'SELECT 1');
PREPARE stmt FROM @sql11;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========== 5. Dias excluidos en docentes ==========
SET @colDias = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'ghe_db' AND TABLE_NAME = 'docentes' AND COLUMN_NAME = 'dias_excluidos');
SET @sqlDias = IF(@colDias = 0,
    'ALTER TABLE docentes ADD COLUMN dias_excluidos VARCHAR(100) DEFAULT NULL AFTER horas_proyecto',
    'SELECT 1');
PREPARE stmt FROM @sqlDias;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========== 6. Tabla indisponibilidades ==========
CREATE TABLE IF NOT EXISTS indisponibilidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    dia_semana VARCHAR(20) NOT NULL,
    sesion INT NOT NULL,
    UNIQUE KEY uniq_indisp (docente_id, dia_semana, sesion),
    CONSTRAINT fk_indisp_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE
);

-- ========== 7. FK tutor si no existe ==========
SET @fkTutor = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_grupo_tutor' AND TABLE_NAME = 'grupos' AND CONSTRAINT_SCHEMA = 'ghe_db');
SET @sql12 = IF(@fkTutor = 0,
    'ALTER TABLE grupos ADD CONSTRAINT fk_grupo_tutor FOREIGN KEY (tutor_id) REFERENCES docentes(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql12;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
