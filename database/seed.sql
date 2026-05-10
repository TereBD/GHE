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
('1º Primaria', 2, 4),
('2º Primaria', 2, 5),
('3º Primaria', 2, 6),
('4º Primaria', 2, 7),
('5º Primaria', 2, 8),
('6º Primaria', 2, 9),
('1º ESO', 3, 10),
('2º ESO', 3, 11),
('3º ESO', 3, 12),
('4º ESO', 3, 13)
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

-- Grupos (nivel_id = 4 = 1º Primaria)
INSERT INTO grupos (nombre, nivel_id, letra, tutor_id) VALUES
('1ºA', 4, 'A', 1),
('1ºB', 4, 'B', 2)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Sesiones por nivel (nivel_id 4 = 1º Primaria, asignatura_ids 1-3)
DELETE FROM nivel_asignatura;
INSERT INTO nivel_asignatura (nivel_id, asignatura_id, sesiones_semana) VALUES
(4, 1, 5),
(4, 2, 5),
(4, 3, 4);

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
