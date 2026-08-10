<?php
/**
 * Explicit operational runner for the additive Endorse V2 schema only.
 * Usage: php tools/run_endorse_v2_migration.php preflight|apply|verify
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$appRoot = rtrim((string) (getenv('ENDORSE_V2_APP_ROOT') ?: '/var/www/html'), '/');
define('BASEPATH', __DIR__);
define('APPPATH', $appRoot . '/application/');
define('FCPATH', $appRoot . '/');
require APPPATH . 'helpers/env_helper.php';

$mode = $argv[1] ?? '';
if (!in_array($mode, ['preflight', 'apply', 'verify'], true)) {
    fwrite(STDERR, "Usage: php tools/run_endorse_v2_migration.php preflight|apply|verify\n");
    exit(2);
}

$db = new mysqli(env('DB_HOSTNAME'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), (int) env('DB_PORT', 3306));
if ($db->connect_errno) {
    fwrite(STDERR, "Database connection failed\n");
    exit(1);
}
$db->set_charset('utf8mb4');

$name = 'create_endorse_v2_core';
$tables = [
    'endorse_v2_runtime_control', 'endorse_v2_content_state', 'endorse_v2_refresh_jobs',
    'endorse_v2_refresh_attempts', 'endorse_v2_refresh_dedupe', 'endorse_v2_metric_observations',
    'endorse_v2_rollup_work', 'endorse_v2_manual_overrides', 'endorse_v2_legacy_import_manifest',
];

$preflight = static function () use ($db, $name) {
    $result = $db->query("SELECT DATABASE() AS database_name, @@version AS mysql_version, @@read_only AS read_only_mode, @@super_read_only AS super_read_only_mode");
    if (!$result) throw new RuntimeException('Preflight query failed');
    $migration = $db->query("SELECT migration FROM app_migrations WHERE migration = '" . $db->real_escape_string($name) . "'");
    echo json_encode(['database' => $result->fetch_assoc(), 'already_recorded' => $migration && $migration->num_rows > 0]), PHP_EOL;
};

try {
    if ($mode === 'preflight') {
        $preflight();
        exit(0);
    }

    if ($mode === 'apply') {
        $preflight();
        $db->query("CREATE TABLE IF NOT EXISTS app_migrations (id INT(11) NOT NULL AUTO_INCREMENT, migration VARCHAR(255) NOT NULL, executed_at DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY migration (migration)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $recorded = $db->query("SELECT migration FROM app_migrations WHERE migration = '" . $db->real_escape_string($name) . "'");
        if ($recorded && $recorded->num_rows > 0) {
            echo "Migration already recorded\n";
            exit(0);
        }
        $file = (string) env('ENDORSE_V2_MIGRATION_FILE', dirname(__DIR__) . '/migrations/' . $name . '.sql');
        $sql = @file_get_contents($file);
        if ($sql === false) throw new RuntimeException('Migration file unavailable');
        $sql = preg_replace('/--.*$/m', '', $sql);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (!$db->query($statement)) throw new RuntimeException('Statement failed: ' . $db->error);
        }
        if (!$db->query("INSERT INTO app_migrations (migration, executed_at) VALUES ('" . $db->real_escape_string($name) . "', UTC_TIMESTAMP())")) throw new RuntimeException('Ledger write failed: ' . $db->error);
        echo "Migration completed\n";
    }

    if ($mode === 'verify' || $mode === 'apply') {
        $existing = [];
        foreach ($tables as $table) {
            $escaped = $db->real_escape_string($table);
            $result = $db->query("SHOW TABLES LIKE '{$escaped}'");
            if ($result && $result->num_rows === 1) $existing[] = $table;
        }
        $recorded = $db->query("SELECT migration FROM app_migrations WHERE migration = '" . $db->real_escape_string($name) . "'");
        $ok = count($existing) === count($tables) && $recorded && $recorded->num_rows === 1;
        echo json_encode(['status' => $ok, 'tables_present' => $existing, 'ledger_recorded' => (bool) ($recorded && $recorded->num_rows)]), PHP_EOL;
        exit($ok ? 0 : 1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
