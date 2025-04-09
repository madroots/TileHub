-- init.sql

CREATE TABLE IF NOT EXISTS tiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(255) DEFAULT NULL,
    group_name VARCHAR(255) NOT NULL,
    position INT NOT NULL,
    group_position INT NOT NULL
);
