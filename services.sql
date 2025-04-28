CREATE DATABASE IF NOT EXISTS cms;
USE cms;

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price VARCHAR(255) NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(255) NOT NULL,
    patient_email VARCHAR(255) NOT NULL,
    patient_phone VARCHAR(15) NOT NULL,
    service_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO services (name, description, price, image_url) VALUES
('Tooth Extraction', 'Post votum promissa memini cuius adeptione cupis; quem politicus est aversione aversi et fuga.', 10000.00, 'images/tooth_extraction.jpg'),
('Teeth Cleaning', 'Professional teeth cleaning to remove plaque and tartar.', 3000.00, 'images/teeth_cleaning.jpg'),
('Dental Fillings', 'Filling cavities to restore tooth structure.', 5000.00, 'images/dental_fillings.jpg');

-- Password is hashed using password_hash() in PHP
-- Example: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- Password: admin123
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'); -- Password: staff123

CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Hashed password
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE follow_ups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    patient_name VARCHAR(255) NOT NULL,
    patient_email VARCHAR(255) NOT NULL,
    patient_phone VARCHAR(20) NOT NULL,
    service_name VARCHAR(255) NOT NULL, -- Store procedure name
    follow_up_date DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'reschedule_requested') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    requested_reschedule_date DATETIME NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);
INSERT INTO follow_ups (patient_id, patient_name, patient_email, patient_phone, service_name, follow_up_date, status) VALUES
(1, 'John Doe', 'johndoe@example.com', '1234567890', 'Teeth Cleaning', '2025-04-15 10:00:00', 'pending'),
(2, 'Jane Smith', 'janesmith@example.com', '0987654321', 'Dental Fillings', '2025-04-16 14:00:00', 'confirmed');