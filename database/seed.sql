USE ghe_db;

INSERT INTO docentes (nombre, apellido, horas_maximas, horas_pat_proyecto) VALUES
('Ana', 'Lopez', 18, 2),
('Juan', 'Garcia', 18, 2),
('Marta', 'Sanchez', 18, 2)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO asignaturas (nombre) VALUES
('Matematicas'),
('Lengua'),
('Ingles')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO grupos (nombre) VALUES
('1A'),
('1B')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

DELETE FROM docente_asig_grupo;
INSERT INTO docente_asig_grupo (docente_id, asignatura_id, grupo_id) VALUES
(1, 1, 1),
(1, 1, 2),
(2, 2, 1),
(2, 2, 2),
(3, 3, 1),
(3, 3, 2);

DELETE FROM distribucion_horaria;
INSERT INTO distribucion_horaria (grupo_id, asignatura_id, sesiones_semana) VALUES
(1, 1, 5),
(1, 2, 5),
(1, 3, 4),
(2, 1, 5),
(2, 2, 5),
(2, 3, 4);

DELETE FROM usuarios;
INSERT INTO usuarios (usuario, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
