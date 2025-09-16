CREATE DATABASE IF NOT EXISTS orgfinpro;
USE orgfinpro;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('president','treasurer','auditor','adviser','dean','admin') DEFAULT 'president',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@example.com', MD5('admin123'), 'admin');
