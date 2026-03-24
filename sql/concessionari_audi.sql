-- =============================================
--  BASE DE DADES: concessionari_audi
--  Concessionari Audi - Izan & Youssef
-- =============================================

CREATE DATABASE IF NOT EXISTS concessionari_audi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE concessionari_audi;

-- ── Taula: usuaris ──
CREATE TABLE IF NOT EXISTS usuaris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuari') NOT NULL DEFAULT 'usuari',
    data_registre DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Taula: vehicles ──
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    any_fabricacio YEAR NOT NULL,
    preu DECIMAL(10,2) NOT NULL,
    quilometres INT NOT NULL DEFAULT 0,
    combustible ENUM('gasolina','dièsel','elèctric','híbrid') NOT NULL,
    color VARCHAR(50) NOT NULL,
    descripcio TEXT,
    imatge VARCHAR(255),
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    data_entrada DATE NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB;

-- ── Taula: clients ──
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    cognoms VARCHAR(150) NOT NULL,
    telefon VARCHAR(20),
    email VARCHAR(150),
    dni VARCHAR(20) UNIQUE NOT NULL,
    adreca TEXT,
    data_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Taula: vendes ──
CREATE TABLE IF NOT EXISTS vendes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    client_id INT NOT NULL,
    venedor_id INT NOT NULL,
    preu_final DECIMAL(10,2) NOT NULL,
    data_venda DATE NOT NULL,
    metode_pagament ENUM('efectiu','finançament','transferència') NOT NULL,
    observacions TEXT,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (venedor_id) REFERENCES usuaris(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Taula: cites ──
CREATE TABLE IF NOT EXISTS cites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    data_cita DATETIME NOT NULL,
    tipus ENUM('prova','revisió','consulta') NOT NULL,
    estat ENUM('pendent','confirmada','completada','cancel·lada') NOT NULL DEFAULT 'pendent',
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
--  DADES INICIALS
-- =============================================

-- Usuaris admin (contrasenya: polonia123)
-- Hash generat amb password_hash('polonia123', PASSWORD_DEFAULT)
INSERT INTO usuaris (nom, email, contrasenya, rol) VALUES
('Youssef', 'youssef@audi.com', '$2y$10$YJ5q3z8K1xN6vM2wR4pFheGbT0dLmCnSoAiU7XkZjW9fHgBvEyD6u', 'admin'),
('Izan', 'izan@audi.com', '$2y$10$YJ5q3z8K1xN6vM2wR4pFheGbT0dLmCnSoAiU7XkZjW9fHgBvEyD6u', 'admin');

-- Vehicles de mostra
INSERT INTO vehicles (model, any_fabricacio, preu, quilometres, combustible, color, descripcio, disponible) VALUES
('Audi A3 Sportback', 2024, 35900.00, 0, 'gasolina', 'Blanc Glacier', 'Nou Audi A3 Sportback amb tecnologia TFSI. Interior premium amb pantalla tàctil MMI de 10.1 polzades.', 1),
('Audi A4 Avant', 2023, 48500.00, 12000, 'dièsel', 'Negre Mytic', 'Audi A4 Avant amb motor TDI de 150 CV. Equipament S line amb llantes de 18 polzades.', 1),
('Audi Q5', 2024, 56200.00, 0, 'híbrid', 'Gris Daytona', 'Nou Audi Q5 TFSI e híbrid endollable. Autonomia elèctrica de 62 km.', 1),
('Audi e-tron GT', 2024, 106800.00, 500, 'elèctric', 'Vermell Tango', 'Audi e-tron GT quattro amb 476 CV. Càrrega ràpida en 23 minuts al 80%.', 1),
('Audi A1 Sportback', 2023, 26700.00, 8500, 'gasolina', 'Blau Turbo', 'Audi A1 Sportback 30 TFSI. Compacte i àgil, ideal per a la ciutat.', 1),
('Audi Q3', 2022, 38900.00, 25000, 'dièsel', 'Verd District', 'Audi Q3 35 TDI S tronic. SUV compacte amb tracció quattro.', 0);

-- Clients de mostra
INSERT INTO clients (nom, cognoms, telefon, email, dni, adreca) VALUES
('Marc', 'García López', '612345678', 'marc.garcia@email.com', '12345678A', 'Carrer Major 15, Barcelona'),
('Laura', 'Martínez Puig', '623456789', 'laura.martinez@email.com', '23456789B', 'Avinguda Diagonal 200, Barcelona'),
('Jordi', 'Fernández Sala', '634567890', 'jordi.fernandez@email.com', '34567890C', 'Plaça Catalunya 5, Girona'),
('Anna', 'Soler Vidal', '645678901', 'anna.soler@email.com', '45678901D', 'Rambla Nova 42, Tarragona');

-- Vendes de mostra
INSERT INTO vendes (vehicle_id, client_id, venedor_id, preu_final, data_venda, metode_pagament, observacions) VALUES
(6, 1, 1, 36500.00, '2024-11-15', 'finançament', 'Finançament a 48 mesos sense interessos.');

-- Cites de mostra
INSERT INTO cites (client_id, vehicle_id, data_cita, tipus, estat, notes) VALUES
(2, 3, '2026-04-01 10:00:00', 'prova', 'confirmada', 'Prova de conducció del Q5 híbrid.'),
(3, 4, '2026-04-02 16:30:00', 'consulta', 'pendent', 'Consulta sobre el e-tron GT i opcions de finançament.');
