DROP DATABASE ip_keeper_db;
CREATE DATABASE IF NOT EXISTS ip_keeper_db;
USE ip_keeper_db;
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip VARCHAR(39) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
CREATE INDEX idx_email ON user(email); 

