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
INSERT IGNORE INTO settings (key_name, value) VALUES ('version', '1.0.0');

-- Check if default tiles have already been created
SET @defaults_created = (SELECT value FROM settings WHERE key_name = 'defaults_created' LIMIT 1);

-- Insert default tiles only if they haven't been created yet
INSERT INTO tiles (title, url, icon, group_id, position)
SELECT 'Wikipedia', 'https://wikipedia.org', '689c79ddb06b2_wikipedia.svg', 
       (SELECT id FROM groups WHERE name = 'Default' LIMIT 1), 1
WHERE @defaults_created IS NULL;

INSERT INTO tiles (title, url, icon, group_id, position)
SELECT 'Internet Archive', 'https://archive.org', '689c7a01ab777_archive.svg', 
       (SELECT id FROM groups WHERE name = 'Default' LIMIT 1), 2
WHERE @defaults_created IS NULL;

INSERT INTO tiles (title, url, icon, group_id, position)
SELECT 'DuckDuckGo', 'https://duckduckgo.com', '689c7a269ed26_duckduckgo.svg', 
       (SELECT id FROM groups WHERE name = 'Default' LIMIT 1), 3
WHERE @defaults_created IS NULL;

-- Set the flag to indicate defaults have been created
INSERT IGNORE INTO settings (key_name, value) VALUES ('defaults_created', 'true');