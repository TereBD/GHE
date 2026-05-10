CREATE DATABASE IF NOT EXISTS ghe_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ghe_db;

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

CREATE TABLE IF NOT EXISTS docentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    tipo ENUM('tutor', 'especialista') NOT NULL DEFAULT 'tutor',
    horas_maximas INT NOT NULL,
    horas_pat INT NOT NULL DEFAULT 0,
    horas_proyecto INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS asignaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    nivel_id INT NOT NULL,
    letra VARCHAR(5) NOT NULL DEFAULT '',
    tutor_id INT DEFAULT NULL,
    CONSTRAINT fk_grupo_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE,
    CONSTRAINT fk_grupo_tutor FOREIGN KEY (tutor_id) REFERENCES docentes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS nivel_asignatura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    sesiones_semana INT NOT NULL,
    UNIQUE KEY uniq_nivel_asig (nivel_id, asignatura_id),
    CONSTRAINT fk_na_nivel FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE,
    CONSTRAINT fk_na_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS docente_asig_grupo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    asignatura_id INT NOT NULL,
    grupo_id INT NOT NULL,
    es_tutoria TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_docente_asig_grupo (docente_id, asignatura_id, grupo_id),
    CONSTRAINT fk_dag_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE,
    CONSTRAINT fk_dag_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE,
    CONSTRAINT fk_dag_grupo FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes') NOT NULL,
    sesion INT NOT NULL,
    asignatura_id INT NOT NULL,
    docente_id INT NOT NULL,
    UNIQUE KEY uniq_grupo_slot (grupo_id, dia_semana, sesion),
    UNIQUE KEY uniq_docente_slot (docente_id, dia_semana, sesion),
    CONSTRAINT fk_horario_grupo FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
    CONSTRAINT fk_horario_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE,
    CONSTRAINT fk_horario_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
