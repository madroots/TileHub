CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    position INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(255) DEFAULT NULL,
    group_id INT NOT NULL,
    position INT NOT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id)
);

-- Insert a default group
INSERT IGNORE INTO groups (name, position) VALUES ('Uncategorized', 1);