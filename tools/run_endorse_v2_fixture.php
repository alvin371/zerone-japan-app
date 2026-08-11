<?php
/**
 * Executes a whitelisted Endorse V2 MySQL fixture against an explicitly marked
 * disposable database. It never accepts an arbitrary SQL filename.
 *
 * Usage:
 *   ENDORSE_V2_TEST_DATABASE=1 php tools/run_endorse_v2_fixture.php pagination
 *   ENDORSE_V2_TEST_DATABASE=1 php tools/run_endorse_v2_fixture.php baseline
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}
if (getenv('ENDORSE_V2_TEST_DATABASE') !== '1') {
    fwrite(STDERR, "Refusing to run: set ENDORSE_V2_TEST_DATABASE=1 for a disposable database.\n");
    exit(2);
}

$fixtures = [
    'pagination' => dirname(__DIR__) . '/tests/Integration/endorse_pagination_regression.sql',
    'baseline' => dirname(__DIR__) . '/tests/Integration/endorse_v2_baseline_contract.sql',
];
$name = $argv[1] ?? '';
if (!isset($fixtures[$name])) {
    fwrite(STDERR, "Usage: php tools/run_endorse_v2_fixture.php pagination|baseline\n");
    exit(2);
}

$appRoot = rtrim((string) (getenv('ENDORSE_V2_APP_ROOT') ?: '/var/www/html'), '/');
define('BASEPATH', __DIR__);
define('APPPATH', $appRoot . '/application/');
define('FCPATH', $appRoot . '/');
require APPPATH . 'helpers/env_helper.php';

$sql = file_get_contents($fixtures[$name]);
if ($sql === false) {
    fwrite(STDERR, "Fixture unavailable\n");
    exit(1);
}

$db = new mysqli(env('DB_HOSTNAME'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), (int) env('DB_PORT', 3306));
if ($db->connect_errno) {
    fwrite(STDERR, "Database connection failed\n");
    exit(1);
}
$db->set_charset('utf8mb4');

if (!$db->multi_query($sql)) {
    fwrite(STDERR, "Fixture failed: " . $db->error . "\n");
    exit(1);
}

$resultSets = [];
do {
    if ($result = $db->store_result()) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $resultSets[] = $rows;
        $result->free();
    }
} while ($db->more_results() && $db->next_result());

if ($db->error) {
    fwrite(STDERR, "Fixture failed: " . $db->error . "\n");
    exit(1);
}
echo json_encode(['fixture' => $name, 'result_sets' => $resultSets], JSON_PRETTY_PRINT), PHP_EOL;
