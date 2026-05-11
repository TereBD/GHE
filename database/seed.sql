USE ghe_db;

-- Etapas
INSERT INTO etapas (nombre, orden) VALUES
('Infantil', 1),
('Primaria', 2),
('ESO', 3)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Niveles
INSERT INTO niveles (nombre, etapa_id, orden) VALUES
('Infantil 3 años', 1, 1),
('Infantil 4 años', 1, 2),
('Infantil 5 años', 1, 3),
('Infantil 3-4 años', 1, 4),
('Infantil 4-5 años', 1, 5),
('1º', 2, 6),
('2º', 2, 7),
('3º', 2, 8),
('4º', 2, 9),
('5º', 2, 10),
('6º', 2, 11),
('1º ESO', 3, 12),
('2º ESO', 3, 13),
('3º ESO', 3, 14),
('4º ESO', 3, 15)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Docentes
INSERT INTO docentes (nombre, apellido, tipo, horas_maximas, horas_pat, horas_proyecto) VALUES
('Ana', 'Lopez', 'tutor', 18, 1, 2),
('Juan', 'Garcia', 'tutor', 18, 1, 2),
('Marta', 'Sanchez', 'especialista', 18, 1, 2)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Asignaturas
INSERT INTO asignaturas (nombre) VALUES
('Lengua'),
('Matematicas'),
('Ingles'),
('Educacion Fisica'),
('Frances'),
('Musica'),
('Religion'),
('ATU/Religión'),
('Geografia e Historia'),
('Biologia'),
('Fisica y Quimica'),
('Tecnologia'),
('Dibujo'),
('Filosofia')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Grupos (nivel_id = 6 = 1º)
INSERT INTO grupos (nombre, nivel_id, letra, tutor_id) VALUES
('1º A', 6, 'A', 1),
('1º B', 6, 'B', 2)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Sesiones por nivel (nivel_id 6 = 1º, asignatura_ids 1-3)
DELETE FROM nivel_asignatura;
INSERT INTO nivel_asignatura (nivel_id, asignatura_id, sesiones_semana) VALUES
(6, 1, 5),
(6, 2, 5),
(6, 3, 4);

-- Asignaciones docente-asignatura-grupo
DELETE FROM docente_asig_grupo;
INSERT INTO docente_asig_grupo (docente_id, asignatura_id, grupo_id) VALUES
(1, 1, 1),
(1, 1, 2),
(2, 2, 1),
(2, 2, 2),
(3, 3, 1),
(3, 3, 2);

-- Usuario admin
DELETE FROM usuarios;
INSERT INTO usuarios (usuario, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
