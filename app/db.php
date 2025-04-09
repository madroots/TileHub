<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function initializeDatabaseSchema($pdo) {
    $tableName = 'tiles';
    $stmt = $pdo->prepare("SHOW TABLES LIKE :table");
    $stmt->execute(['table' => $tableName]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $createTableSql = "
            CREATE TABLE IF NOT EXISTS $tableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                url VARCHAR(255) NOT NULL,
                icon VARCHAR(255) DEFAULT NULL,
                group_name VARCHAR(255) NOT NULL,
                position INT NOT NULL,
                group_position INT NOT NULL
            )
        ";
        $pdo->exec($createTableSql);
    }
}
?>