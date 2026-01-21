<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration Controller
 * Handles database migrations for the application
 */
class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * List all available migrations
     */
    public function index()
    {
        // Check if user is logged in and is admin (optional security)
        if (!isset($_SESSION['is_login']) || !$_SESSION['is_login']) {
            echo json_encode(['status' => false, 'msg' => 'Unauthorized. Please login first.']);
            return;
        }

        $migrations = $this->_get_migration_files();
        $executed = $this->_get_executed_migrations();

        $data = [
            'status' => true,
            'migrations' => [],
            'total' => count($migrations),
            'executed' => count($executed),
            'pending' => count($migrations) - count($executed)
        ];

        foreach ($migrations as $file) {
            $name = basename($file, '.sql');
            $data['migrations'][] = [
                'name' => $name,
                'file' => $file,
                'executed' => in_array($name, $executed),
                'executed_at' => $this->_get_migration_date($name)
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Run all pending migrations
     */
    public function run()
    {
        header('Content-Type: application/json');

        // Check if user is logged in
        if (!isset($_SESSION['is_login']) || !$_SESSION['is_login']) {
            echo json_encode(['status' => false, 'msg' => 'Unauthorized. Please login first.']);
            return;
        }

        // Ensure migrations table exists
        $this->_ensure_migrations_table();

        $migrations = $this->_get_migration_files();
        $executed = $this->_get_executed_migrations();

        $results = [];
        $success_count = 0;
        $error_count = 0;

        foreach ($migrations as $file) {
            $name = basename($file, '.sql');

            if (in_array($name, $executed)) {
                $results[] = [
                    'name' => $name,
                    'status' => 'skipped',
                    'msg' => 'Already executed'
                ];
                continue;
            }

            $result = $this->_run_migration($file, $name);
            $results[] = $result;

            if ($result['status'] === 'success') {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        echo json_encode([
            'status' => $error_count === 0,
            'msg' => "Migrations completed. Success: $success_count, Errors: $error_count",
            'results' => $results
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Run a specific migration by name
     */
    public function run_single($name = null)
    {
        header('Content-Type: application/json');

        // Check if user is logged in
        if (!isset($_SESSION['is_login']) || !$_SESSION['is_login']) {
            echo json_encode(['status' => false, 'msg' => 'Unauthorized. Please login first.']);
            return;
        }

        if (empty($name)) {
            $name = $_GET['name'] ?? '';
        }

        if (empty($name)) {
            echo json_encode(['status' => false, 'msg' => 'Migration name is required']);
            return;
        }

        // Ensure migrations table exists
        $this->_ensure_migrations_table();

        $file = FCPATH . 'migrations/' . $name . '.sql';

        if (!file_exists($file)) {
            echo json_encode(['status' => false, 'msg' => 'Migration file not found: ' . $name]);
            return;
        }

        $executed = $this->_get_executed_migrations();
        if (in_array($name, $executed)) {
            echo json_encode(['status' => false, 'msg' => 'Migration already executed: ' . $name]);
            return;
        }

        $result = $this->_run_migration($file, $name);

        echo json_encode([
            'status' => $result['status'] === 'success',
            'msg' => $result['msg'],
            'migration' => $name
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Rollback a specific migration (if rollback file exists)
     */
    public function rollback($name = null)
    {
        header('Content-Type: application/json');

        // Check if user is logged in
        if (!isset($_SESSION['is_login']) || !$_SESSION['is_login']) {
            echo json_encode(['status' => false, 'msg' => 'Unauthorized. Please login first.']);
            return;
        }

        if (empty($name)) {
            $name = $_GET['name'] ?? '';
        }

        if (empty($name)) {
            echo json_encode(['status' => false, 'msg' => 'Migration name is required']);
            return;
        }

        // Check if rollback file exists
        $rollback_file = FCPATH . 'migrations/rollback_' . $name . '.sql';
        if (!file_exists($rollback_file)) {
            echo json_encode(['status' => false, 'msg' => 'Rollback file not found for: ' . $name]);
            return;
        }

        // Read and execute rollback SQL
        $sql = file_get_contents($rollback_file);
        if (empty(trim($sql))) {
            echo json_encode(['status' => false, 'msg' => 'Rollback file is empty']);
            return;
        }

        try {
            // Execute rollback SQL
            $statements = $this->_parse_sql_statements($sql);
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $this->db->query($statement);
                }
            }

            // Remove from migrations table
            $this->db->delete('app_migrations', ['migration' => $name]);

            echo json_encode([
                'status' => true,
                'msg' => 'Rollback successful for: ' . $name
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'msg' => 'Rollback failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get list of migration files
     */
    private function _get_migration_files()
    {
        $migration_path = FCPATH . 'migrations/';

        if (!is_dir($migration_path)) {
            mkdir($migration_path, 0755, true);
            return [];
        }

        $files = glob($migration_path . '*.sql');

        // Filter out rollback files
        $files = array_filter($files, function($file) {
            return strpos(basename($file), 'rollback_') !== 0;
        });

        sort($files);
        return $files;
    }

    /**
     * Get list of executed migrations
     */
    private function _get_executed_migrations()
    {
        // Check if migrations table exists
        if (!$this->db->table_exists('app_migrations')) {
            return [];
        }

        $query = $this->db->select('migration')->get('app_migrations');
        $result = $query->result_array();

        return array_column($result, 'migration');
    }

    /**
     * Get migration execution date
     */
    private function _get_migration_date($name)
    {
        if (!$this->db->table_exists('app_migrations')) {
            return null;
        }

        $query = $this->db->select('executed_at')
            ->where('migration', $name)
            ->get('app_migrations');

        $row = $query->row_array();
        return $row ? $row['executed_at'] : null;
    }

    /**
     * Ensure migrations table exists with correct schema
     */
    private function _ensure_migrations_table()
    {
        // Use a different table name to avoid conflicts with CI's built-in migrations
        $table_name = 'app_migrations';

        if (!$this->db->table_exists($table_name)) {
            $sql = "CREATE TABLE `$table_name` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `migration` VARCHAR(255) NOT NULL,
                `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migration` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

            $this->db->query($sql);
        }
    }

    /**
     * Run a single migration file
     */
    private function _run_migration($file, $name)
    {
        $sql = file_get_contents($file);

        if (empty(trim($sql))) {
            return [
                'name' => $name,
                'status' => 'error',
                'msg' => 'Migration file is empty'
            ];
        }

        try {
            // Parse and execute SQL statements
            $statements = $this->_parse_sql_statements($sql);

            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $this->db->query($statement);
                }
            }

            // Record migration as executed
            $this->db->insert('app_migrations', [
                'migration' => $name,
                'executed_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'name' => $name,
                'status' => 'success',
                'msg' => 'Migration executed successfully'
            ];

        } catch (Exception $e) {
            return [
                'name' => $name,
                'status' => 'error',
                'msg' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse SQL file into individual statements
     */
    private function _parse_sql_statements($sql)
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Split by semicolon (but not inside quotes)
        $statements = [];
        $current = '';
        $in_string = false;
        $string_char = '';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];

            if ($in_string) {
                $current .= $char;
                if ($char === $string_char && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $in_string = false;
                }
            } else {
                if ($char === '"' || $char === "'") {
                    $in_string = true;
                    $string_char = $char;
                    $current .= $char;
                } else if ($char === ';') {
                    $trimmed = trim($current);
                    if (!empty($trimmed)) {
                        $statements[] = $trimmed;
                    }
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
        }

        // Add last statement if exists
        $trimmed = trim($current);
        if (!empty($trimmed)) {
            $statements[] = $trimmed;
        }

        return $statements;
    }
}
