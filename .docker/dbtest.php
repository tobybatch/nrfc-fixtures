<?php
// Get the database URL from environment variables
$databaseUrl = getenv('DATABASE_URL');

// Parse the URL to extract connection details
$dbParts = parse_url($databaseUrl);

// Extract components
$dbHost = $dbParts['host'];
$dbPort = $dbParts['port'] ?? ''; // Handle case where port might not be specified
$dbUser = $dbParts['user'];
$dbPass = $dbParts['pass'];
$dbName = ltrim($dbParts['path'], '/'); // Remove leading slash from path

// Construct DSN (Data Source Name)
$dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
echo $databaseUrl;
echo $dsn;

echo "Testing DB:";

try {
    $pdo = new \PDO($dsn, $dbUser, $dbPass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ]);
} catch(\Exception $ex) {
    switch ($ex->getCode()) {
        case 1045:
            // we can immediately stop here and show the error message
            echo 'Access denied (1045)';
            die(1);
        case 1049:
            // error "Unknown database (1049)" can be ignored, the database will be created by nrfcfixtures
            return;
        // a lot of errors share the same meaningless error code zero
        case 0:
            // this error includes the database name, so we can only search for the static part of the error message
            if (stripos($ex->getMessage(), 'SQLSTATE[HY000] [1049] Unknown database') !== false) {
                // error "Unknown database (1049)" can be ignored, the database will be created by nrfcfixtures
                return;
            }
            switch ($ex->getMessage()) {
                // eg. no response (fw) - the startup script should retry it a couple of times
                case 'SQLSTATE[HY000] [2002] Operation timed out':
                    echo 'Operation timed out (0-2002)';
                    die(4);
                // special case "localhost" with a stopped db server (should not happen in docker compose setup)
                case 'SQLSTATE[HY000] [2002] No such file or directory':
                    echo 'Connection could not be established (0-2002)';
                    die(5);
                // using IP with stopped db server - the startup script should retry it a couple of times
                case 'SQLSTATE[HY000] [2002] Connection refused':
                    echo 'Connection refused (0-2002)';
                    die(5);
            }
            echo $ex->getMessage() . " (0)";
            die(7);
        default:
            // unknown error
            echo $ex->getMessage() . " (?)";
            die(10);
    }
}
