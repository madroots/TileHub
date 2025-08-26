-- Create groups table
CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    position INT NOT NULL DEFAULT 0
);

-- Create tiles table
CREATE TABLE IF NOT EXISTS tiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(255) DEFAULT NULL,
    group_id INT NOT NULL,
    position INT NOT NULL,
    FOREIGN KEY (group_id) REFERENCES groups(id)
);

-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    value TEXT DEFAULT NULL
);

-- Insert default group if it doesn't exist
INSERT IGNORE INTO groups (name, position) VALUES ('Default', 1);

-- Insert default settings
INSERT IGNORE INTO settings (key_name, value) VALUES ('dashboard_title', 'TileHub Dashboard');
INSERT IGNORE INTO settings (key_name, value) VALUES ('show_settings_button', 'true');