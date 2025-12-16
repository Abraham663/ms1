<?php
// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'hostel_mess');

// Attempt connection with robust error handling and helpful guidance
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection_error = null;
$hosts_to_try = [DB_HOST];
if (DB_HOST === 'localhost') $hosts_to_try[] = '127.0.0.1';

$conn = null;
foreach ($hosts_to_try as $host) {
    try {
        $conn = new mysqli($host, DB_USER, DB_PASSWORD, DB_NAME);
        if ($conn->connect_errno) {
            throw new mysqli_sql_exception($conn->connect_error, $conn->connect_errno);
        }
        // Use utf8mb4 for full Unicode support
        $conn->set_charset('utf8mb4');
        break;
    } catch (mysqli_sql_exception $e) {
        $connection_error = $e;
        $conn = null;
    }
}

if (!$conn) {
    // Log full error server-side for debugging
    error_log('DB connection error: ' . ($connection_error ? $connection_error->getMessage() : 'unknown'));

    // Show friendly troubleshooting instructions to the developer/user
    http_response_code(500);
    echo '<h2>Database connection error</h2>';
    echo '<p>Could not connect to the database. Please check the following:</p>';
    echo '<ul>';
    echo '<li>Is MySQL running? Start MySQL from the XAMPP Control Panel.</li>';
    echo '<li>Are the credentials in <strong>config.php</strong> correct (user/password/database)?</li>';
    echo '<li>Has the database <strong>' . htmlspecialchars(DB_NAME) . '</strong> been created (import <code>database.sql</code>)?</li>';
    echo '</ul>';
    echo '<p>For details see the PHP error log. The full error was logged but not displayed here for security.</p>';
    exit;
}
?>
