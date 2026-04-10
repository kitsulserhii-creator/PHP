<?php
declare(strict_types=1);

/**
 * Database Configuration
 * Central configuration file for database connection
 * This file should be updated with actual credentials when deploying
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'phpp_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

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
