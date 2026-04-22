<?php
declare(strict_types=1);

/**
 * Database Configuration
 * Central configuration file for database connection
 * Supports environment variables from .env file or falls back to default values
 */

// Load environment variables from .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable if not already set
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Database configuration constants (read from environment or use defaults)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'phpp_portfolio');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/**
 * Get PDO database connection
 * 
 * @return PDO Configured PDO instance
 * @throws PDOException on connection failure
 */
function getDbConnection(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose database credentials in error messages
            error_log('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Database connection failed. Please check configuration.');
        }
    }
    
    return $pdo;
}

/**
 * Execute a query with error handling
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return PDOStatement
 */
function dbQuery(string $sql, array $params = []): PDOStatement
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch one row from database
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|false
 */
function dbFetchOne(string $sql, array $params = [])
{
    return dbQuery($sql, $params)->fetch();
}

/**
 * Fetch all rows from database
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array
 */
function dbFetchAll(string $sql, array $params = []): array
{
    return dbQuery($sql, $params)->fetchAll();
}
